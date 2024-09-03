<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\NodeAnalyzer;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\HtmlString;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use TomasVotruba\Bladestan\TemplateCompiler\ValueObject\RenderTemplateWithParameters;

final class LaravelViewFunctionMatcher
{
    public function __construct(
        private readonly TemplateFilePathResolver $templateFilePathResolver,
        private readonly ViewDataParametersAnalyzer $viewDataParametersAnalyzer,
        private readonly MagicViewWithCallParameterResolver $magicViewWithCallParameterResolver
    ) {
    }

    /**
     * @return RenderTemplateWithParameters[]
     */
    public function match(FuncCall|ClassMethod $funcCall, Scope $scope): array
    {
        $funcName = $funcCall->name;

        if (! $funcName instanceof Name && ! $funcName instanceof Identifier) {
            return [];
        }

        if ($funcName instanceof Name) {
            $funcName = $scope->resolveName($funcName);
        } elseif ($funcName instanceof Identifier) {
            $funcName = $funcName->toString();
        }

        if ($funcName === 'view' && $funcCall instanceof FuncCall) {
            return $this->matchView($funcCall, $scope);
        } elseif ($funcName === 'content' && $funcCall instanceof ClassMethod) {
            return $this->matchContent($funcCall, $scope);
        }
        return [];
    }

    /**
     * @return RenderTemplateWithParameters[]
     */
    private function matchContent(ClassMethod $funcCall, Scope $scope): array
    {
        $returnType = $funcCall->getReturnType();
        if (! $returnType instanceof Name) {
            return [];
        }

        if ($returnType->toString() !== Content::class) {
            return [];
        }

        $statements = $funcCall->getStmts();

        if ($statements === null) {
            return [];
        }

        foreach ($statements as $stmt) {
            if (! $stmt instanceof Return_) {
                continue;
            }
            $newExpression = $stmt->expr;
            if ($newExpression instanceof New_) {
                $viewName = null;
                $viewWith = new Array_();

                // Collect
                foreach ($newExpression->getArgs() as $argument) {
                    $argName = $argument->name?->toString();
                    if ($argName === 'view') {
                        $viewName = $argument->value;
                    }
                    if ($argName === 'with') {
                        $viewWith = $this->viewDataParametersAnalyzer->resolveParametersArray($argument, $scope);
                    }
                }
                if ($viewName !== null) {
                        $result = [];
                        $resolvedTemplateFilePaths = $this->templateFilePathResolver->resolveExistingFilePaths(
                            $viewName,
                            $scope
                        );
                        foreach ($resolvedTemplateFilePaths as $resolvedTemplateFilePath) {
                            $result[] = new RenderTemplateWithParameters($resolvedTemplateFilePath, $viewWith);
                        }
                        return $result;
                }
            }
        }
        return [];
    }

    /**
     * @return RenderTemplateWithParameters[]
     */
    private function matchView(FuncCall $funcCall, Scope $scope): array
    {
        // TODO: maybe make sure this function is coming from Laravel

        if (count($funcCall->getArgs()) < 1) {
            return [];
        }

        $template = $funcCall->getArgs()[0]
->value;

        $resolvedTemplateFilePaths = $this->templateFilePathResolver->resolveExistingFilePaths($template, $scope);
        if ($resolvedTemplateFilePaths === []) {
            return [];
        }

        $args = $funcCall->getArgs();

        if (count($args) !== 2) {
            $parametersArray = new Array_();
        } else {
            $parametersArray = $this->viewDataParametersAnalyzer->resolveParametersArray($args[1], $scope);
        }

        $parametersArray->items = $this->magicViewWithCallParameterResolver->resolve(
            $funcCall
        ) + $parametersArray->items;

        if ($scope->isInClass() && $scope->getClassReflection()->is(Component::class)) {
            $type = new New_(new FullyQualified(HtmlString::class));
            $parametersArray->items[] = new ArrayItem($type, new String_('slot'));
            $type = new New_(new FullyQualified(ComponentAttributeBag::class));
            $parametersArray->items[] = new ArrayItem($type, new String_('attributes'));
        }

        $result = [];
        foreach ($resolvedTemplateFilePaths as $resolvedTemplateFilePath) {
            $result[] = new RenderTemplateWithParameters($resolvedTemplateFilePath, $parametersArray);
        }

        return $result;
    }
}

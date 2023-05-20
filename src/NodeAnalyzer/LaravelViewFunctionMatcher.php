<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\NodeAnalyzer;

use Illuminate\Support\HtmlString;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
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
    public function match(FuncCall $funcCall, Scope $scope): array
    {
        $funcName = $funcCall->name;
        if (! $funcName instanceof Name) {
            return [];
        }

        $funcName = $scope->resolveName($funcName);
        if ($funcName !== 'view') {
            return [];
        }

        // TODO: maybe make sure this function is coming from Laravel

        if (count($funcCall->getArgs()) < 1) {
            return [];
        }

        $template = $funcCall->getArgs()[0]->value;

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

<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\NodeAnalyzer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
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

        $result = [];
        foreach ($resolvedTemplateFilePaths as $resolvedTemplateFilePath) {
            $result[] = new RenderTemplateWithParameters($resolvedTemplateFilePath, $parametersArray);
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\NodeAnalyzer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use TomasVotruba\Bladestan\TemplateCompiler\ValueObject\RenderTemplateWithParameters;

final class LaravelContentMatcher
{
    public function __construct(
        private readonly TemplateFilePathResolver $templateFilePathResolver,
        private readonly ViewDataParametersAnalyzer $viewDataParametersAnalyzer,
    ) {
    }

    /**
     * @return RenderTemplateWithParameters[]
     */
    public function match(New_ $new, Scope $scope): array
    {
        $viewName = null;
        $viewWith = new Array_();
        foreach ($new->getArgs() as $argument) {
            $argName = (string) $argument->name;
            if ($argName === 'view') {
                $viewName = $argument->value;
            } elseif ($argName === 'with') {
                $viewWith = $this->viewDataParametersAnalyzer->resolveParametersArray($argument, $scope);
            }
        }

        if ($viewName === null) {
            return [];
        }

        $result = [];
        $resolvedTemplateFilePaths = $this->templateFilePathResolver->resolveExistingFilePaths($viewName, $scope);
        foreach ($resolvedTemplateFilePaths as $resolvedTemplateFilePath) {
            $result[] = new RenderTemplateWithParameters($resolvedTemplateFilePath, $viewWith);
        }

        return $result;
    }
}

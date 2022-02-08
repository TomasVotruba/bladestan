<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use Symplify\TemplatePHPStanCompiler\ValueObject\RenderTemplateWithParameters;

use function count;

final class LaravelViewFunctionMatcher
{
    public function __construct(
        private TemplateFilePathResolver $templateFilePathResolver,
        private ViewDataParametersAnalyzer $viewDataParametersAnalyzer,
        private MagicViewWithCallParameterResolver $magicViewWithCallParameterResolver
    ) {
    }

    /** @return RenderTemplateWithParameters[] */
    public function match(Node\Expr\FuncCall $funcCall, Scope $scope): array
    {
        $funcName = $funcCall->name;

        if (! $funcName instanceof Node\Name) {
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

        $resolvedTemplateFilePaths = $this->templateFilePathResolver->resolveExistingFilePaths(
            $template,
            $scope,
        );

        if ($resolvedTemplateFilePaths === []) {
            return [];
        }

        $args = $funcCall->getArgs();

        if (count($args) !== 2) {
            $parametersArray = new Node\Expr\Array_();
        } else {
            $parametersArray = $this->viewDataParametersAnalyzer->resolveParametersArray($args[1], $scope);
        }

        $parametersArray->items = $this->magicViewWithCallParameterResolver->resolve($funcCall) + $parametersArray->items;

        $result = [];
        foreach ($resolvedTemplateFilePaths as $resolvedTemplateFilePath) {
            $result[] = new RenderTemplateWithParameters($resolvedTemplateFilePath, $parametersArray);
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use TomasVotruba\Bladestan\NodeAnalyzer\BladeViewMethodsMatcher;
use TomasVotruba\Bladestan\NodeAnalyzer\LaravelViewFunctionMatcher;
use TomasVotruba\Bladestan\TemplateCompiler\Rules\TemplateRulesRegistry;

/**
 * @implements Rule<Node>
 */
final class BladeRule implements Rule
{
    /**
     * @param Rule[] $rules
     */
    public function __construct(
        array $rules,
        private readonly BladeViewMethodsMatcher $bladeViewMethodsMatcher,
        private readonly LaravelViewFunctionMatcher $laravelViewFunctionMatcher,
        private readonly ViewRuleHelper $viewRuleHelper
    ) {
        $this->viewRuleHelper->setRegistry(new TemplateRulesRegistry($rules));
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof FuncCall) {
            return $this->processLaravelViewFunction($node, $scope);
        }

        if ($node instanceof MethodCall) {
            return $this->processBladeView($node, $scope);
        }

        return [];
    }

    /**
     * @return RuleError[]
     */
    private function processLaravelViewFunction(FuncCall $funcCall, Scope $scope): array
    {
        $renderTemplatesWithParameters = $this->laravelViewFunctionMatcher->match($funcCall, $scope);

        return $this->viewRuleHelper->processNode($funcCall, $scope, $renderTemplatesWithParameters);
    }

    /**
     * @return RuleError[]
     */
    private function processBladeView(MethodCall $methodCall, Scope $scope): array
    {
        $renderTemplatesWithParameters = $this->bladeViewMethodsMatcher->match($methodCall, $scope);

        return $this->viewRuleHelper->processNode($methodCall, $scope, $renderTemplatesWithParameters);
    }
}

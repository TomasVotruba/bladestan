<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use TomasVotruba\Bladestan\NodeAnalyzer\BladeViewMethodsMatcher;
use TomasVotruba\Bladestan\NodeAnalyzer\LaravelContentMatcher;
use TomasVotruba\Bladestan\NodeAnalyzer\LaravelViewFunctionMatcher;
use TomasVotruba\Bladestan\TemplateCompiler\Rules\TemplateRulesRegistry;
use TomasVotruba\Bladestan\ViewRuleHelper;

/**
 * @implements Rule<Node>
 * @see \TomasVotruba\Bladestan\Tests\Rules\BladeRuleTest
 */
final class BladeRule implements Rule
{
    /**
     * @param list<Rule> $rules
     */
    public function __construct(
        array $rules,
        private readonly BladeViewMethodsMatcher $bladeViewMethodsMatcher,
        private readonly LaravelContentMatcher $laravelContentMatcher,
        private readonly LaravelViewFunctionMatcher $laravelViewFunctionMatcher,
        private readonly ViewRuleHelper $viewRuleHelper
    ) {
        $this->viewRuleHelper->setRegistry(new TemplateRulesRegistry($rules));
    }

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof StaticCall || $node instanceof FuncCall) {
            return $this->processLaravelViewFunction($node, $scope);
        }

        if ($node instanceof MethodCall) {
            return $this->processBladeView($node, $scope);
        }

        if ($node instanceof New_) {
            return $this->processLaravelContent($node, $scope);
        }

        return [];
    }

    /**
     * @return RuleError[]
     */
    private function processLaravelContent(New_ $new, Scope $scope): array
    {
        $renderTemplatesWithParameters = $this->laravelContentMatcher->match($new, $scope);

        return $this->viewRuleHelper->processNode($new, $scope, $renderTemplatesWithParameters);
    }

    /**
     * @return RuleError[]
     */
    private function processLaravelViewFunction(CallLike $callLike, Scope $scope): array
    {
        $renderTemplatesWithParameters = $this->laravelViewFunctionMatcher->match($callLike, $scope);

        return $this->viewRuleHelper->processNode($callLike, $scope, $renderTemplatesWithParameters);
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

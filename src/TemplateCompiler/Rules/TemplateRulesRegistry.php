<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\TemplateCompiler\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Rules\DirectRegistry;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\Methods\CallMethodsRule;
use PHPStan\Rules\Rule;
use TomasVotruba\Bladestan\TemplateCompiler\Reflection\PrivatesAccessor;

/**
 * @api
 */
final class TemplateRulesRegistry extends DirectRegistry
{
    /**
     * @var string[]
     */
    private const EXCLUDED_RULES = [
        'Symplify\PHPStanRules\Rules\ForbiddenFuncCallRule',
        'Symplify\PHPStanRules\Rules\NoDynamicNameRule',
    ];

    /**
     * @param array<Rule<Node>> $rules
     */
    public function __construct(array $rules)
    {
        $activeRules = $this->filterActiveRules($rules);
        parent::__construct($activeRules);
    }

    /**
     * @template TNode as \PhpParser\Node
     * @param class-string<TNode> $nodeType
     * @return array<Rule<TNode>>
     */
    public function getRules(string $nodeType): array
    {
        $activeRules = parent::getRules($nodeType);

        // only fix in a weird test case setup
        if (defined('PHPUNIT_COMPOSER_INSTALL') && $nodeType === MethodCall::class) {
            $privatesAccessor = new PrivatesAccessor();

            foreach ($activeRules as $activeRule) {
                if (! $activeRule instanceof CallMethodsRule) {
                    continue;
                }

                /** @var FunctionCallParametersCheck $check */
                $check = $privatesAccessor->getPrivateProperty(
                    $activeRule,
                    'parametersCheck'
                );

                $privatesAccessor->setPrivateProperty($check, 'checkArgumentTypes', true);
            }
        }

        return $activeRules;
    }

    /**
     * @param array<Rule<Node>> $rules
     * @return array<Rule<Node>>
     */
    private function filterActiveRules(array $rules): array
    {
        $activeRules = [];

        foreach ($rules as $rule) {
            foreach (self::EXCLUDED_RULES as $excludedRule) {
                if ($rule instanceof $excludedRule) {
                    continue 2;
                }
            }

            $activeRules[] = $rule;
        }

        return $activeRules;
    }
}

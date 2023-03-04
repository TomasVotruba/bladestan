<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Rules;

use PHPStan\Rules\Cast\EchoRule;
use PHPStan\Rules\Operators\InvalidBinaryOperationRule;
use PHPStan\Rules\Rule;
use PHPStan\Rules\Variables\DefinedVariableRule;
use PHPStan\Testing\RuleTestCase;
use TomasVotruba\Bladestan\Compiler\BladeToPHPCompiler;
use TomasVotruba\Bladestan\ErrorReporting\Blade\TemplateErrorsFactory;
use TomasVotruba\Bladestan\NodeAnalyzer\BladeViewMethodsMatcher;
use TomasVotruba\Bladestan\NodeAnalyzer\LaravelViewFunctionMatcher;
use TomasVotruba\Bladestan\Rules\BladeRule;
use TomasVotruba\Bladestan\Rules\ViewRuleHelper;
use TomasVotruba\Bladestan\TemplateCompiler\PHPStan\FileAnalyserProvider;
use TomasVotruba\Bladestan\TemplateCompiler\TypeAnalyzer\TemplateVariableTypesResolver;

use function array_merge;

/**
 * @extends RuleTestCase<BladeRule>
 */
class LaravelViewFunctionRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new BladeRule(
            [
                self::getContainer()->getByType(InvalidBinaryOperationRule::class),
                self::getContainer()->getByType(EchoRule::class),
                self::getContainer()->getByType(DefinedVariableRule::class),
            ],
            self::getContainer()->getByType(BladeViewMethodsMatcher::class),
            self::getContainer()->getByType(LaravelViewFunctionMatcher::class),
            new ViewRuleHelper(
                self::getContainer()->getByType(TemplateVariableTypesResolver::class),
                self::getContainer()->getByType(FileAnalyserProvider::class),
                self::getContainer()->getByType(TemplateErrorsFactory::class),
                self::getContainer()->getByType(BladeToPHPCompiler::class),
            )
        );
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/laravel-view-function.php'], [
            [
                'Binary operation "+" between string and 10 results in an error.',
                9,
            ],
            [
                'Binary operation "+" between string and \'bar\' results in an error.',
                9,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                11,
            ],
            [
                'Binary operation "+" between string and 6 results in an error.',
                13,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                15,
            ],
            [
                'Binary operation "+" between int and \'foo\' results in an error.',
                15,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                18,
            ],
            [
                'Variable $bar might not be defined.',
                18,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                20,
            ],
            [
                'Binary operation "+" between \'10bar\' and 30 results in an error.',
                20,
            ],
            [
                'Binary operation "+" between string and 20 results in an error.',
                20,
            ],
            [
                'Variable $bar might not be defined.',
                20,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                22,
            ],
            [
                'Binary operation "+" between \'10bar\' and 30 results in an error.',
                22,
            ],
            [
                'Undefined variable: $bar',
                22,
            ],
        ]);
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return array_merge(parent::getAdditionalConfigFiles(), [__DIR__ . '/config/configWithTemplatePaths.neon']);
    }
}

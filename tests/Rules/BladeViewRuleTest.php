<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Rules;

use PHPStan\Rules\Operators\InvalidBinaryOperationRule;
use PHPStan\Rules\Rule;
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
class BladeViewRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new BladeRule(
            [self::getContainer()->getByType(InvalidBinaryOperationRule::class)],
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
        $this->analyse([__DIR__ . '/data/view-factory.php'], [
            [
                'Binary operation "+" between string and 10 results in an error.',
                13,
            ],
            [
                'Binary operation "+" between string and \'bar\' results in an error.',
                13,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                14,
            ],
            [
                'Binary operation "+" between string and \'bar\' results in an error.',
                14,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                15,
            ],
            [
                'Binary operation "+" between string and \'bar\' results in an error.',
                15,
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

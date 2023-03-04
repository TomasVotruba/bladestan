<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TomasVotruba\Bladestan\Rules\BladeRule;

final class LaravelViewFunctionRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(BladeRule::class);
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
        return [__DIR__ . '/config/configWithTemplatePaths.neon'];
    }
}

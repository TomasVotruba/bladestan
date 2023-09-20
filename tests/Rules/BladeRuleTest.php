<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Rules;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use TomasVotruba\Bladestan\Rules\BladeRule;

final class BladeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(BladeRule::class);
    }

    /**
     * @param mixed[] $expectedErrorsWithLines
     */
    #[DataProvider('provideData')]
    public function testRule(string $analysedFile, array $expectedErrorsWithLines): void
    {
        $this->analyse([$analysedFile], $expectedErrorsWithLines);
    }

    public static function provideData(): Iterator
    {
        yield [
            __DIR__ . '/Fixture/view-factory.php',
            [
                ['Binary operation "+" between string and 10 results in an error.', 13],
                ['Binary operation "+" between string and \'bar\' results in an error.', 13],
                ['Binary operation "+" between string and 10 results in an error.', 16],
                ['Binary operation "+" between string and \'bar\' results in an error.', 16],
                ['Binary operation "+" between string and 10 results in an error.', 19],
                ['Binary operation "+" between string and \'bar\' results in an error.', 19],
            ],
        ];

        // @todo instead of one huge file with 20 errors, there should be similar errors together, just 2-3 errors per file to make easier debugging and extending
        yield [
            __DIR__ . '/Fixture/laravel-view-function.php',
            [
                ['Binary operation "+" between string and 10 results in an error.', 9],
                ['Binary operation "+" between string and \'bar\' results in an error.', 9],
                ['Binary operation "+" between string and 10 results in an error.', 13],
                // this one is related somehow to "tests/Rules/templates/nested/directory", without it in paths fails; should work without it too
                ['Binary operation "+" between string and 6 results in an error.', 15],
                ['Binary operation "+" between string and 10 results in an error.', 19],
                ['Binary operation "+" between int and \'foo\' results in an error.', 19],
                ['Binary operation "+" between string and 10 results in an error.', 22],
                ['Variable $bar might not be defined.', 22],
                ['Binary operation "+" between string and 10 results in an error.', 24],
                ['Binary operation "+" between \'10bar\' and 30 results in an error.', 24],
                ['Binary operation "+" between string and 20 results in an error.', 24],
                ['Variable $bar might not be defined.', 24],
                ['Binary operation "+" between string and 10 results in an error.', 28],
                ['Binary operation "+" between \'10bar\' and 30 results in an error.', 28],
                ['Undefined variable: $bar', 28],
                ['Binary operation "+" between string and 10 results in an error.', 33],
                ['Variable $bar might not be defined.', 33],
                ['Binary operation "+" between string and \'bar\' results in an error.', 35],
                ['If condition is always true.', 35],
                ['Binary operation "+" between string and \'bar\' results in an error.', 35],
                ['Variable $foos might not be defined.', 43],
            ],
        ];

        yield [__DIR__ . '/Fixture/view-render-int.php', []];

        yield [__DIR__ . '/Fixture/skip-form-errors.php', []];

        yield [__DIR__ . '/Fixture/laravel-response-function.php', [
            ['Binary operation "+" between string and 10 results in an error.', 9],
            ['Binary operation "+" between string and \'bar\' results in an error.', 9],
        ]];

        yield [__DIR__ . '/Fixture/laravel-component-method.php', [
            ['Binary operation "+" between string and 10 results in an error.', 17],
        ]];

        yield [__DIR__ . '/Fixture/laravel-component-function.php', [
            ['Binary operation "+" between string and 10 results in an error.', 17],
        ]];

        yield [__DIR__ . '/Fixture/laravel-mailable-method.php', [
            ['Binary operation "+" between string and 10 results in an error.', 16],
            ['Binary operation "+" between string and \'bar\' results in an error.', 16],
        ]];

        yield [__DIR__ . '/Fixture/laravel-mailable-public-properties.php', [
            ['Binary operation "+" between string and 10 results in an error.', 14],
            ['Binary operation "+" between string and \'bar\' results in an error.', 14],
        ]];

        yield [__DIR__ . '/Fixture/laravel-mail_message-method.php', [
            ['Binary operation "+" between string and 10 results in an error.', 16],
            ['Binary operation "+" between string and \'bar\' results in an error.', 16],
        ]];

        yield [
            __DIR__ . '/Fixture/laravel-view-include.php',
            [['Binary operation "+" between string and \'bar\' results in an error.', 9]],
        ];
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/config/configured_extension.neon'];
    }
}

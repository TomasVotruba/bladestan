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
                13,
            ],
            [
                'Binary operation "+" between string and 6 results in an error.',
                15,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                19,
            ],
            [
                'Binary operation "+" between int and \'foo\' results in an error.',
                19,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                22,
            ],
            [
                'Variable $bar might not be defined.',
                22,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                24,
            ],
            [
                'Binary operation "+" between \'10bar\' and 30 results in an error.',
                24,
            ],
            [
                'Binary operation "+" between string and 20 results in an error.',
                24,
            ],
            [
                'Variable $bar might not be defined.',
                24,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                28,
            ],
            [
                'Binary operation "+" between \'10bar\' and 30 results in an error.',
                28,
            ],
            [
                'Undefined variable: $bar',
                28,
            ],
        ]);
    }

<<<<<<< HEAD
<<<<<<< HEAD
=======
=======
>>>>>>> 3aeabd2 (fixup! misc)
    public static function provideData(): \Iterator
    {
        // @todo instead of one huge file with 20 errors, there should be similar errors together, just 2-3 errors per file to make easier debugging and extending
        yield [
            [__DIR__ . '/data/laravel-view-function.php'],
            [
                ['Binary operation "+" between string and 10 results in an error.', 9],
                ['Binary operation "+" between string and \'bar\' results in an error.', 9],
                ['Binary operation "+" between string and 10 results in an error.', 13],
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
<<<<<<< HEAD

=======
>>>>>>> 3aeabd2 (fixup! misc)

                // this one is related somehow to "tests/Rules/templates/nested/directory", without it in paths fails; should work without it too
                ['Binary operation "+" between string and 6 results in an error.', 15],
            ],
        ];
    }

<<<<<<< HEAD
>>>>>>> eb1c8a4 (fixup! misc)
=======
>>>>>>> 3aeabd2 (fixup! misc)
    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/config/configWithTemplatePaths.neon'];
    }
}

<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Rules;

use Iterator;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use TomasVotruba\Bladestan\Rules\BladeRule;

final class BladeViewRuleTest extends RuleTestCase
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
            __DIR__ . '/data/view-factory.php',
            [
                ['Binary operation "+" between string and 10 results in an error.', 13],
                ['Binary operation "+" between string and \'bar\' results in an error.', 13],
                ['Binary operation "+" between string and 10 results in an error.', 16],
                ['Binary operation "+" between string and \'bar\' results in an error.', 16],
                ['Binary operation "+" between string and 10 results in an error.', 19],
                ['Binary operation "+" between string and \'bar\' results in an error.', 19],
            ],
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

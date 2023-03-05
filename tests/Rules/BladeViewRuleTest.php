<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TomasVotruba\Bladestan\Rules\BladeRule;

final class BladeViewRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(BladeRule::class);
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
                16,
            ],
            [
                'Binary operation "+" between string and \'bar\' results in an error.',
                16,
            ],
            [
                'Binary operation "+" between string and 10 results in an error.',
                19,
            ],
            [
                'Binary operation "+" between string and \'bar\' results in an error.',
                19,
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

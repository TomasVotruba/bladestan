<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\TemplateCompiler;

use PHPStan\Analyser\Error;

/**
 * @see \TomasVotruba\Bladestan\Tests\TemplateCompiler\ErrorFilterTest
 */
final class ErrorFilter
{
    /**
     * @var string[]
     */
    private const ERRORS_TO_IGNORE_REGEXES = [
        '#Call to function unset\(\) contains undefined variable \$loop#',
        '#Variable \$loop in PHPDoc tag @var does not exist#',
        '#Anonymous function has an unused use (.*?)#',
    ];

    /**
     * @param Error[] $ruleErrors
     * @return Error[]
     */
    public function filterErrors(array $ruleErrors): array
    {
        foreach ($ruleErrors as $key => $ruleError) {
            foreach (self::ERRORS_TO_IGNORE_REGEXES as $errorToIgnoreRegex) {
                if (! preg_match($errorToIgnoreRegex, $ruleError->getMessage())) {
                    continue;
                }

                unset($ruleErrors[$key]);
            }
        }

        return $ruleErrors;
    }
}

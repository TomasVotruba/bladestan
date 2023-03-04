<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\TemplateCompiler;

use PHPStan\Analyser\Error;

/**
 * @see \Bladestan\TemplateCompiler\Tests\ErrorSkipperTest
 */
final class ErrorSkipper
{
    /**
     * @param Error[] $errors
     * @param string[] $errorIgnores
     * @return Error[]
     */
    public function skipErrors(array $errors, array $errorIgnores): array
    {
        $filteredErrors = [];

        foreach ($errors as $error) {
            foreach ($errorIgnores as $errorIgnore) {
                $result = preg_match($errorIgnore, $error->getMessage(), $matches);
                if ($result !== false) {
                    continue 2;
                }
            }

            $filteredErrors[] = $error;
        }

        return $filteredErrors;
    }
}

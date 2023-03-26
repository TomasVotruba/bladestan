<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\ErrorReporting\Blade;

use PHPStan\Analyser\Error;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use TomasVotruba\Bladestan\ValueObject\PhpFileContentsWithLineMap;

final class TemplateErrorsFactory
{
    /**
     * @param Error[] $errors
     * @return RuleError[]
     */
    public function createErrors(
        array $errors,
        int $phpFileLine,
        string $phpFilePath,
        PhpFileContentsWithLineMap $phpFileContentsWithLineMap
    ): array {
        $ruleErrors = [];

        $phpToTemplateLines = $phpFileContentsWithLineMap->getPhpToTemplateLines();

        foreach ($errors as $error) {
            $phpLineNumberInTemplate = (int) $error->getLine();

            $fileNameAndTemplateLine = $phpToTemplateLines[$phpLineNumberInTemplate] ?? null;

            if ($fileNameAndTemplateLine === null) {
                $fileNameAndTemplateLine = $this->resolveNearestPhpLine($phpToTemplateLines, $phpLineNumberInTemplate);
            }

            $ruleErrors[] = RuleErrorBuilder::message($error->getMessage())
                ->file($phpFilePath)
                ->line($phpFileLine)
                ->metadata([
                    'template_file_path' => array_key_first($fileNameAndTemplateLine),
                    'template_line' => current($fileNameAndTemplateLine),
                ])
                ->build();
        }

        return $ruleErrors;
    }

    /**
     * Sometimes one template line can be replaced with 2 PHP lines.
     * For example `foreach` loop. This method tries to find the previous
     * template line in that case.
     *
     * @param array<int, array<string, int>> $phpToTemplateLines
     *
     * @return array<string, int>
     */
    private function resolveNearestPhpLine(array $phpToTemplateLines, int $desiredLine): array
    {
        $lastTemplateLine = [
            '' => 1,
        ];

        foreach ($phpToTemplateLines as $phpLine => $templateLine) {
            if ($desiredLine > $phpLine) {
                $lastTemplateLine = $templateLine;
                continue;
            }

            return $lastTemplateLine;
        }

        return $lastTemplateLine;
    }
}

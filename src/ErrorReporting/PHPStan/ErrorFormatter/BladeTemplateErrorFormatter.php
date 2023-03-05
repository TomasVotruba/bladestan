<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\ErrorReporting\PHPStan\ErrorFormatter;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalyseCommand;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\Output;
use PHPStan\File\RelativePathHelper;

/**
 * @see https://github.com/phpstan/phpstan-src/blob/master/src/Command/ErrorFormatter/TableErrorFormatter.php
 */
final class BladeTemplateErrorFormatter
{
    public function __construct(
        private readonly RelativePathHelper $relativePathHelper,
        private readonly bool $showTipsOfTheDay,
        private readonly ?string $editorUrl,
    ) {
    }

    public function formatErrors(
        AnalysisResult $analysisResult,
        Output $output,
    ): int {
        $projectConfigFile = 'phpstan.neon.dist';
        if ($analysisResult->getProjectConfigFile() !== null) {
            $projectConfigFile = $this->relativePathHelper->getRelativePath($analysisResult->getProjectConfigFile());
        }

        $style = $output->getStyle();

        if (! $analysisResult->hasErrors() && ! $analysisResult->hasWarnings()) {
            $style->success('No errors');
            if ($this->showTipsOfTheDay) {
                if ($analysisResult->isDefaultLevelUsed()) {
                    $output->writeLineFormatted('💡 Tip of the Day:');
                    $output->writeLineFormatted(sprintf(
                        "PHPStan is performing only the most basic checks.\nYou can pass a higher rule level through the <fg=cyan>--%s</> option\n(the default and current level is %d) to analyse code more thoroughly.",
                        AnalyseCommand::OPTION_LEVEL,
                        AnalyseCommand::DEFAULT_LEVEL,
                    ));
                    $output->writeLineFormatted('');
                }
            }

            return 0;
        }

        /** @var array<string, Error[]> $fileErrors */
        $fileErrors = [];
        foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
            if (! isset($fileErrors[$fileSpecificError->getFile()])) {
                $fileErrors[$fileSpecificError->getFile()] = [];
            }

            $fileErrors[$fileSpecificError->getFile()][] = $fileSpecificError;
        }

        foreach ($fileErrors as $file => $errors) {
            $rows = [];
            foreach ($errors as $error) {
                $message = $error->getMessage();
                if ($error->getTip() !== null) {
                    $tip = $error->getTip();
                    $tip = str_replace('%configurationFile%', $projectConfigFile, $tip);
                    $message .= "\n💡 " . $tip;
                }

                if (is_string($this->editorUrl)) {
                    $message .= "\n✏️  " . str_replace(['%file%', '%line%'], [$error->getTraitFilePath() ?? $error->getFilePath(), (string) $error->getLine()], $this->editorUrl);
                }

                $templateFilePath = $error->getMetadata()['template_file_path'] ?? null;
                $templateLine = $error->getMetadata()['template_line'] ?? null;

                if ($templateFilePath && $templateLine) {
                    $message .= ' <fg=magenta;options=bold>(template: ' . $templateFilePath . ', line: ' . $templateLine . ')</>';
                }

                $rows[] = [
                    (string) $error->getLine(),
                    $message,
                ];
            }

            $relativeFilePath = $this->relativePathHelper->getRelativePath($file);

            $style->table(['Line', $relativeFilePath], $rows);
        }

        if (count($analysisResult->getNotFileSpecificErrors()) > 0) {
            $style->table(['', 'Error'], array_map(static fn (string $error): array => ['', $error], $analysisResult->getNotFileSpecificErrors()));
        }

        $warningsCount = count($analysisResult->getWarnings());
        if ($warningsCount > 0) {
            $style->table(['', 'Warning'], array_map(static fn (string $warning): array => ['', $warning], $analysisResult->getWarnings()));
        }

        $finalMessage = sprintf($analysisResult->getTotalErrorsCount() === 1 ? 'Found %d error' : 'Found %d errors', $analysisResult->getTotalErrorsCount());
        if ($warningsCount > 0) {
            $finalMessage .= sprintf($warningsCount === 1 ? ' and %d warning' : ' and %d warnings', $warningsCount);
        }

        if ($analysisResult->getTotalErrorsCount() > 0) {
            $style->error($finalMessage);
        } else {
            $style->warning($finalMessage);
        }

        return $analysisResult->getTotalErrorsCount() > 0 ? 1 : 0;
    }
}

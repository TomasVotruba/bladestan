<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Compiler;

use Illuminate\Support\Str;
use TomasVotruba\Bladestan\Configuration\Configuration;

final class FileNameAndLineNumberAddingPreCompiler
{
    /**
     * @var string
     */
    private const PHP_SINGLE_LINE_COMMENT_REGEX = '#^/\*\*.*?\*/$#';

    /**
     * @see https://regex101.com/r/SfpjMO/1
     * @var string
     */
    private const PHP_PARTIAL_COMMENT = '#^(\* )?@(var|param|method|extends|implements|template) +(.*?) \$[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*#';

    private string $fileName;

    public function __construct(
        private readonly Configuration $configuration
    ) {
    }

    public function compileString(string $value): string
    {
        if ($this->fileName === '') {
            return '';
        }

        if ($this->fileName === '0') {
            return '';
        }

        $lines = explode(PHP_EOL, $value);

        $lineNumber = 1;

        foreach ($lines as $key => $line) {
            if (! $this->shouldSkip($line)) {
                $lines[$key] = sprintf('/** file: %s, line: %d */', $this->fileName, $lineNumber) . $line;
            }

            ++$lineNumber;
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * @todo remove fluent, make service method with fileName as argument to avoid miss-use
     */
    public function setFileName(string $fileName): self
    {
        foreach ($this->configuration->getTemplatePaths() as $templatePath) {
            $templatePath = rtrim($templatePath, '/') . '/';

            if (str_contains($fileName, $templatePath)) {
                $fileName = Str::after($fileName, $templatePath);
                break;
            }
        }

        $this->fileName = $fileName;

        return $this;
    }

    private function shouldSkip(string $line): bool
    {
        if (in_array(trim($line), ['', '/**', '*/'], true)) {
            return true;
        }

        if (preg_match(self::PHP_SINGLE_LINE_COMMENT_REGEX, trim($line))) {
            return true;
        }

        return preg_match(self::PHP_PARTIAL_COMMENT, trim($line)) === 1;
    }
}

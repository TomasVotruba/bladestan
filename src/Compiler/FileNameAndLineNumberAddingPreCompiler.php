<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Compiler;

use Illuminate\Support\Str;

use function explode;
use function implode;
use function in_array;
use function preg_match;
use function rtrim;
use function sprintf;
use function str_contains;
use function trim;

use const PHP_EOL;

final class FileNameAndLineNumberAddingPreCompiler
{
    private const PHP_SINGLE_LINE_COMMENT_REGEX = '#^/\*\*.*?\*/$#';

    /** @see https://regex101.com/r/GnVnZE/1 */
    private const PHP_PARTIAL_COMMENT = '#(\* )?@\w+ \b(.*?) \$\w+#';

    private string $fileName;

    /**
     * @param string[] $templatePaths
     */
    public function __construct(
        private array $templatePaths,
    ) {
    }

    public function compileString(string $value): string
    {
        if (! $this->fileName) {
            return '';
        }

        $lines = explode(PHP_EOL, $value);

        $lineNumber = 1;

        foreach ($lines as $key => $line) {
            if (! $this->shouldSkip($line)) {
                $lines[$key] = sprintf('/** file: %s, line: %d */', $this->fileName, $lineNumber) . $line;
            }

            $lineNumber++;
        }

        return implode(PHP_EOL, $lines);
    }

    public function setFileName(string $fileName): self
    {
        foreach ($this->templatePaths as $templatePath) {
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

<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Compiler;

use function explode;
use function implode;
use function sprintf;

use const PHP_EOL;

final class FileNameAndLineNumberAddingPreCompiler
{
    private string $fileName;

    public function compileString(string $value): string
    {
        $lines = explode(PHP_EOL, $value);

        $lineNumber = 1;

        foreach ($lines as $key => $line) {
            $lines[$key] = sprintf('/** file: %s, line: %d */', $this->fileName, $lineNumber) . $line;

            $lineNumber++;
        }

        return implode(PHP_EOL, $lines);
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }
}

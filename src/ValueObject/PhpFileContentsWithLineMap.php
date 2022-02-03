<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\ValueObject;

/** @see \Symplify\TemplatePHPStanCompiler\ValueObject\PhpFileContentsWithLineMap */
final class PhpFileContentsWithLineMap
{
    /**
     * @param array<int, array<string, int>> $phpToTemplateLines
     */
    public function __construct(
        private string $phpFileContents,
        private array $phpToTemplateLines
    ) {
    }

    public function getPhpFileContents(): string
    {
        return $this->phpFileContents;
    }

    /**
     * @return array<int, array<string, int>>
     */
    public function getPhpToTemplateLines(): array
    {
        return $this->phpToTemplateLines;
    }
}

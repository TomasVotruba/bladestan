<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\ValueObject;

final class CompiledTemplate {
    public function __construct(
        private readonly string $filePath,
        private readonly PhpFileContentsWithLineMap $lineMap
    ) {}

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getLineMap(): PhpFileContentsWithLineMap
    {
        return $this->lineMap;
    }

}

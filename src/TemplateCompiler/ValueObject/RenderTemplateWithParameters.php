<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\TemplateCompiler\ValueObject;

use PhpParser\Node\Expr\Array_;

final class RenderTemplateWithParameters
{
    public function __construct(
        private readonly string $templateFilePath,
        private readonly Array_ $parametersArray
    ) {
    }

    public function getTemplateFilePath(): string
    {
        return $this->templateFilePath;
    }

    public function getParametersArray(): Array_
    {
        return $this->parametersArray;
    }
}

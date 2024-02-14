<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\TemplateCompiler\ValueObject;

use PhpParser\Node\Expr\Array_;
use PHPStan\Type\Type;

final class RenderTemplateWithParameters
{
    public function __construct(
        private readonly string $templateFilePath,
        private readonly Array_ $parametersArray,
        private readonly ?Type $calledOnType = null
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

    public function getCalledOnType(): ?Type
    {
        return $this->calledOnType;
    }
}

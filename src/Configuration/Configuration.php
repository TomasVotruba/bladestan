<?php

namespace TomasVotruba\Bladestan\Configuration;

use Webmozart\Assert\Assert;

final class Configuration
{
    /**
     * @var string
     */
    public const TEMPLATE_PATHS = 'template_paths';

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private readonly array $parameters
    ) {
        Assert::keyExists($this->parameters, self::TEMPLATE_PATHS);
    }

    /**
     * @return string[]
     */
    public function getTemplatePaths(): array
    {
        return $this->parameters[self::TEMPLATE_PATHS];
    }
}

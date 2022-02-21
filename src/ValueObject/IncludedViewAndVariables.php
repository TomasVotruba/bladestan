<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\ValueObject;

final class IncludedViewAndVariables
{
    /** @param array<string, string> $variablesAndValues */
    public function __construct(private string $includedViewName, private array $variablesAndValues)
    {
    }

    /**
     * @return array<string, string>
     */
    public function getVariablesAndValues(): array
    {
        return $this->variablesAndValues;
    }

    public function getIncludedViewName(): string
    {
        return $this->includedViewName;
    }
}

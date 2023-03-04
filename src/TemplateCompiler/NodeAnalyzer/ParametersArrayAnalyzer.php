<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\TemplateCompiler\NodeAnalyzer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantStringType;

final class ParametersArrayAnalyzer
{
    /**
     * @return string[]
     */
    public function resolveStringKeys(Array_ $array, Scope $scope): array
    {
        $stringKeyNames = [];

        foreach ($array->items as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            if ($arrayItem->key === null) {
                continue;
            }

            $keyType = $scope->getType($arrayItem->key);
            if (! $keyType instanceof ConstantStringType) {
                continue;
            }

            $stringKeyNames[] = $keyType->getValue();
        }

        return $stringKeyNames;
    }
}

<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\NodeAnalyzer;

use PhpParser\Node;
use PHPStan\Analyser\Scope;

final class ViewDataParametersAnalyzer
{
    public function __construct(private CompactFunctionCallParameterResolver $compactFunctionCallParameterResolver)
    {
    }

    public function resolveParametersArray(Node\Arg $arg, Scope $scope): Node\Expr\Array_
    {
        $secondArgValue = $arg->value;

        if ($secondArgValue instanceof Node\Expr\Array_) {
            return $secondArgValue;
        }

        if ($secondArgValue instanceof Node\Expr\FuncCall && $secondArgValue->name instanceof Node\Name) {
            $funcName = $scope->resolveName($secondArgValue->name);

            if ($funcName === 'compact') {
                return $this->compactFunctionCallParameterResolver->resolveParameters($secondArgValue);
            }
        }

        return new Node\Expr\Array_();
    }
}

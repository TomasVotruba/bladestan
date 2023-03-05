<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;

final class CompactFunctionCallParameterResolver
{
    public function resolveParameters(FuncCall $compactCall): Array_
    {
        $resultArray = new Array_();

        $funcArgs = $compactCall->getArgs();

        foreach ($funcArgs as $arg) {
            if (! $arg instanceof Arg) {
                continue;
            }

            if (! $arg->value instanceof String_) {
                continue;
            }

            $variableName = $arg->value->value;

            $resultArray->items[] = new ArrayItem(new Variable($variableName), new String_($variableName));
        }

        return $resultArray;
    }
}

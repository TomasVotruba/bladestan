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
    public function resolveParameters(FuncCall $funcCall): Array_
    {
        $resultArray = new Array_();

        $funcArgs = $funcCall->getArgs();

        foreach ($funcArgs as $funcArg) {
            if (! $funcArg instanceof Arg) {
                continue;
            }

            if (! $funcArg->value instanceof String_) {
                continue;
            }

            $variableName = $funcArg->value->value;

            $resultArray->items[] = new ArrayItem(new Variable($variableName), new String_($variableName));
        }

        return $resultArray;
    }
}

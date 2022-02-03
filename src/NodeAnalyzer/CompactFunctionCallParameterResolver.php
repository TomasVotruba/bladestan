<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\NodeAnalyzer;

use PhpParser\Node;

final class CompactFunctionCallParameterResolver
{
    public function resolveParameters(Node\Expr\FuncCall $compactCall): Node\Expr\Array_
    {
        $resultArray = new Node\Expr\Array_();

        $funcArgs = $compactCall->getArgs();

        foreach ($funcArgs as $arg) {
            if ((! $arg instanceof Node\Arg) || (! $arg->value instanceof Node\Scalar\String_)) {
                continue;
            }

            $variableName = $arg->value->value;

            $resultArray->items[] = new Node\Expr\ArrayItem(new Node\Expr\Variable($variableName), new Node\Scalar\String_($variableName));
        }

        return $resultArray;
    }
}

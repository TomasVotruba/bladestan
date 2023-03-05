<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\NodeAnalyzer;

use Illuminate\Support\Str;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;

final class MagicViewWithCallParameterResolver
{
    /**
     * @return Node\Expr\ArrayItem[]
     */
    public function resolve(FuncCall $funcCall): array
    {
        $result = [];

        if (! $funcCall->hasAttribute('viewWithArgs')) {
            return $result;
        }

        /** @var array<string, Node\Arg[]> $viewWithArgs */
        $viewWithArgs = $funcCall->getAttribute('viewWithArgs');

        foreach ($viewWithArgs as $variableName => $args) {
            if ($variableName === 'with') {
                $result[] = new ArrayItem($args[1]->value, $args[0]->value);
            } elseif (str_starts_with($variableName, 'with')) {
                $result[] = new ArrayItem($args[0]->value, new String_(Str::camel(substr($variableName, 4))));
            }
        }

        return $result;
    }
}

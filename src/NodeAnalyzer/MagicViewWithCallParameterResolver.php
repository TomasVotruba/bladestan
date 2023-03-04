<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\NodeAnalyzer;

use Illuminate\Support\Str;
use PhpParser\Node;

use function str_starts_with;
use function substr;

final class MagicViewWithCallParameterResolver
{
    /**
     * @return Node\Expr\ArrayItem[]
     */
    public function resolve(Node\Expr\FuncCall $funcCall): array
    {
        $result = [];

        if (! $funcCall->hasAttribute('viewWithArgs')) {
            return $result;
        }

        /** @var array<string, Node\Arg[]> $viewWithArgs */
        $viewWithArgs = $funcCall->getAttribute('viewWithArgs');

        foreach ($viewWithArgs as $variableName => $args) {
            if ($variableName === 'with') {
                $result[] = new Node\Expr\ArrayItem($args[1]->value, $args[0]->value);
            } elseif (str_starts_with($variableName, 'with')) {
                $result[] = new Node\Expr\ArrayItem($args[0]->value, new Node\Scalar\String_(Str::camel(substr($variableName, 4))));
            }
        }

        return $result;
    }
}

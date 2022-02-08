<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\NodeAnalyzer;

use Illuminate\Support\Str;
use PhpParser\Node;
use Symplify\Astral\Naming\SimpleNameResolver;

use function str_starts_with;
use function substr;

final class MagicViewWithCallParameterResolver
{
    public function __construct(
        private SimpleNameResolver $nameResolver,
    ) {
    }

    /** @return Node\Expr\ArrayItem[] */
    public function resolve(Node\Expr\FuncCall $funcCall): array
    {
        $result      = [];
        $currentNode = $funcCall;

        while ($currentNode->hasAttribute('parent')) {
            $parent = $currentNode->getAttribute('parent');

            if (! $parent instanceof Node\Expr\MethodCall) {
                break;
            }

            $methodName = $this->nameResolver->getName($parent->name);

            if ($methodName !== null) {
                if ($methodName === 'with') {
                    $result[] = new Node\Expr\ArrayItem($parent->getArgs()[1]->value, $parent->getArgs()[0]->value);
                } elseif (str_starts_with($methodName, 'with')) {
                    $result[] = new Node\Expr\ArrayItem($parent->getArgs()[0]->value, new Node\Scalar\String_(Str::camel(substr($methodName, 4))));
                }
            }

            $currentNode = $parent;
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\PHPParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class RemoveEscapeFunctionNodeVisitor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node): ?Node
    {
        if (($node instanceof Node\Expr\FuncCall) && $node->name instanceof Node\Name && $node->name->toString() === 'e') {
            return $node->getArgs()[0]->value;
        }

        return null;
    }
}

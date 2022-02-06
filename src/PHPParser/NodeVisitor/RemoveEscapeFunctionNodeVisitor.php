<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\PHPParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeVisitorAbstract;

use function count;

final class RemoveEscapeFunctionNodeVisitor extends NodeVisitorAbstract
{
    /** @return null|Node|Node[] */
    public function leaveNode(Node $node): null|Node|array
    {
        if (! $node instanceof Node\Stmt\Echo_) {
            return null;
        }

        $funcCallExp = $node->exprs[0];

        if (! $funcCallExp instanceof Node\Expr\FuncCall) {
            return null;
        }

        if (! $funcCallExp->name instanceof Node\Name) {
            return null;
        }

        if ($funcCallExp->name->toString() !== 'e' && count($funcCallExp->getArgs()) < 1) {
            return null;
        }

        if ($funcCallExp->getArgs()[0]->getDocComment() !== null) {
            $docNop = new Nop();
            $docNop->setDocComment($funcCallExp->getArgs()[0]->getDocComment());

            return [$docNop, new Node\Stmt\Echo_([$funcCallExp->getArgs()[0]->value])];
        }

        if ($node->getDocComment() !== null) {
            $docNop = new Nop();
            $docNop->setDocComment($node->getDocComment());

            return [$docNop, new Node\Stmt\Echo_([$funcCallExp->getArgs()[0]->value])];
        }

        return new Node\Stmt\Echo_([$funcCallExp->getArgs()[0]->value]);
    }
}

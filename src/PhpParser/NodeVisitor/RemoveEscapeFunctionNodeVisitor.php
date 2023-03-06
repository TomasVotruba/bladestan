<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeVisitorAbstract;

final class RemoveEscapeFunctionNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @return Node|Node[]|null
     */
    public function leaveNode(Node $node): null|Node|array
    {
        if (! $node instanceof Echo_) {
            return null;
        }

        $funcCallExp = $node->exprs[0];

        if (! $funcCallExp instanceof FuncCall) {
            return null;
        }

        if (! $funcCallExp->name instanceof Name) {
            return null;
        }

        if ($funcCallExp->name->toString() !== 'e' && count($funcCallExp->getArgs()) < 1) {
            return null;
        }

        if ($funcCallExp->getArgs()[0]->getDocComment() !== null) {
            $docNop = new Nop();
            $docNop->setDocComment($funcCallExp->getArgs()[0]->getDocComment());

            return [$docNop, new Echo_([$funcCallExp->getArgs()[0]->value])];
        }

        if ($node->getDocComment() !== null) {
            $docNop = new Nop();
            $docNop->setDocComment($node->getDocComment());

            return [$docNop, new Echo_([$funcCallExp->getArgs()[0]->value])];
        }

        return new Echo_([$funcCallExp->getArgs()[0]->value]);
    }
}

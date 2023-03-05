<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\PHPParser\NodeVisitor;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\NodeVisitorAbstract;
use TomasVotruba\Bladestan\ValueObject\Loop;

final class AddLoopVarTypeToForeachNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @return Node[]|null
     */
    public function leaveNode(Node $node): ?array
    {
        if (! $node instanceof Foreach_) {
            return null;
        }

        if (! $node->expr instanceof Variable) {
            return null;
        }

        $foreachedVariableName = $node->expr->name;

        if (! is_string($foreachedVariableName)) {
            return null;
        }

        if ($foreachedVariableName !== '__currentLoopData') {
            return null;
        }

        $docNop = new Nop();
        $docNop->setDocComment(new Doc(sprintf(
            '/** @var %s $%s */',
            '\\' . Loop::class,
            'loop'
        )));

        // Add `$loop` var doc type as the first statement
        array_unshift($node->stmts, $docNop);

        // `endforeach` also has a doc comment. Remove that before adding our unset.
        array_pop($node->stmts);

        // Add `unset($loop)` at the end of the loop
        // to prevent accessing this variable outside of loop
        $node->stmts[] = new Unset_([new Variable('loop')]);

        return null;
    }
}

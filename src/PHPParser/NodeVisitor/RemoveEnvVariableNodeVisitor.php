<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\PHPParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class RemoveEnvVariableNodeVisitor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node): ?int
    {
        // Remove `$__env->...(...)`
        if (
            ($node instanceof Node\Stmt\Expression) && $this->isEnvCall($node->expr) ||
            (($node instanceof Node\Stmt\Echo_) && $this->isEnvCall($node->exprs[0]))
        ) {
            return NodeTraverser::REMOVE_NODE;
        }

        if (
            $node instanceof Node\Stmt\Expression &&
            $node->expr instanceof Node\Expr\Assign &&
            $node->expr->var instanceof Node\Expr\Variable &&
            $node->expr->var->name === 'loop'
        ) {
            return NodeTraverser::REMOVE_NODE;
        }

        return null;
    }

    private function isEnvCall(Node $node): bool
    {
        return $node instanceof MethodCall &&
            $node->var instanceof Node\Expr\Variable &&
            $node->var->name === '__env';
    }
}

<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\PHPParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use PHPStan\ShouldNotHappenException;
use Webmozart\Assert\Assert;

final class ViewFunctionArgumentsNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var array<string, mixed>>
     */
    private array $stack = [];

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->stack = [];

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof FuncCall && $node->name instanceof Node\Name\FullyQualified && $node->name->toCodeString() === '\view') {
            if (count($this->stack) > 0) {
                $node->setAttribute(
                    'viewWithArgs',
                    $this->stack[array_key_last($this->stack)],
                );
            }
        }

        if ($node instanceof Node\FunctionLike) {
            // is this necessary?
            $this->stack['foo'] = null;
        }

        if ($node instanceof MethodCall && $node->name instanceof Identifier && str_starts_with($node->name->name, 'with')) {
            $rootViewNode = $node;
            $namesAndArgs = [];
            while ($rootViewNode->var instanceof MethodCall) {
                if ($rootViewNode->name instanceof Identifier) {
                    $methodName = $rootViewNode->name->toString();
                } else {
                    // @todo test
                    break;
                }

                $namesAndArgs[$methodName] = $rootViewNode->args;
                $rootViewNode = $rootViewNode->var;
            }

            if ($rootViewNode->name instanceof Identifier) {
                $methodName = $rootViewNode->name->toString();
            } else {
                // @todo test
                throw new ShouldNotHappenException();
            }

            $namesAndArgs[$methodName] = $rootViewNode->args;

            if (
                $rootViewNode->var instanceof FuncCall &&
                $rootViewNode->var->name instanceof Node\Name &&
                $rootViewNode->var->name->toCodeString() === 'view' &&
                count($rootViewNode->var->getArgs()) > 0
            ) {
                $cacheKey = $rootViewNode->var->getAttribute('phpstan_cache');
                Assert::string($cacheKey);
                Assert::notEmpty($cacheKey);

                //  = md5(json_encode($rootViewNode->var));

                if (! array_key_exists($cacheKey, $this->stack)) {
                    $this->stack[$cacheKey] = $namesAndArgs;
                }
            }
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if (! $node instanceof FuncCall) {
            return null;
        }

        if ($node->name instanceof Node\Name && $node->name->toCodeString() === 'view') {
            array_pop($this->stack);
        }

        return null;
    }
}

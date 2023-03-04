<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\PHPParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Symplify\Astral\Naming\SimpleNameResolver;

use function array_key_exists;
use function array_key_last;
use function array_pop;
use function count;
use function json_encode;
use function md5;
use function str_starts_with;

class ViewFunctionArgumentsNodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private SimpleNameResolver $nameResolver,
    ) {
    }

    /**
     * @var array<string, mixed>>
     */
    private array $stack = [];

    public function beforeTraverse(array $nodes): ?array
    {
        $this->stack = [];

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name\FullyQualified && $node->name->toCodeString() === '\view') {
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

        if ($node instanceof Node\Expr\MethodCall && $node->name instanceof Node\Identifier && str_starts_with($node->name->name, 'with')) {
            $rootViewNode = $node;
            $namesAndArgs = [];
            while ($rootViewNode->var instanceof Node\Expr\MethodCall) {
                $namesAndArgs[$this->nameResolver->getName($rootViewNode->name)] = $rootViewNode->args;

                $rootViewNode = $rootViewNode->var;
            }

            $namesAndArgs[$this->nameResolver->getName($rootViewNode->name)] = $rootViewNode->args;

            if (
                $rootViewNode->var instanceof Node\Expr\FuncCall &&
                $rootViewNode->var->name instanceof Node\Name &&
                $rootViewNode->var->name->toCodeString() === 'view' &&
                count($rootViewNode->var->getArgs()) > 0
            ) {
                $key = md5(json_encode($rootViewNode->var));

                if (! array_key_exists($key, $this->stack)) {
                    $this->stack[$key] = $namesAndArgs;
                }
            }
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if (! $node instanceof Node\Expr\FuncCall) {
            return null;
        }

        if ($node->name instanceof Node\Name && $node->name->toCodeString() === 'view') {
            array_pop($this->stack);
        }

        return null;
    }
}

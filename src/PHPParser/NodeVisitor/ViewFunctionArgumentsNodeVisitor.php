<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\PHPParser\NodeVisitor;

use Nette\Utils\Json;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use PHPStan\ShouldNotHappenException;
use Webmozart\Assert\Assert;

final class ViewFunctionArgumentsNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var array<string, null|array<string, Node\Arg[]>>
     */
    private array $argsByMethodNameStack = [];

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->argsByMethodNameStack = [];

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof FuncCall && $node->name instanceof FullyQualified && $node->name->toCodeString() === '\view' && $this->argsByMethodNameStack !== []) {
            $node->setAttribute(
                'viewWithArgs',
                $this->argsByMethodNameStack[array_key_last($this->argsByMethodNameStack)],
            );
        }

        if ($node instanceof FunctionLike) {
            // is this necessary?
            $this->argsByMethodNameStack['foo'] = null;
        }

        if ($node instanceof MethodCall && $node->name instanceof Identifier && str_starts_with($node->name->name, 'with')) {
            $rootViewNode = $node;
            $namesAndArgs = [];
            while ($rootViewNode->var instanceof MethodCall) {
                if ($rootViewNode->name instanceof Identifier) {
                    $methodName = $rootViewNode->name->toString();
                } else {
                    break;
                }

                $namesAndArgs[$methodName] = $rootViewNode->getArgs();
                $rootViewNode = $rootViewNode->var;
            }

            if ($rootViewNode->name instanceof Identifier) {
                $methodName = $rootViewNode->name->toString();
            } else {
                throw new ShouldNotHappenException();
            }

            $namesAndArgs[$methodName] = $rootViewNode->getArgs();

            if (
                $rootViewNode->var instanceof FuncCall &&
                $rootViewNode->var->name instanceof Name &&
                $rootViewNode->var->name->toCodeString() === 'view' &&
                $rootViewNode->var->getArgs() !== []
            ) {
                $cacheKey = Json::encode($rootViewNode->var);
                Assert::string($cacheKey);
                Assert::notEmpty($cacheKey);

                //  = md5(json_encode($rootViewNode->var));

                if (! array_key_exists($cacheKey, $this->argsByMethodNameStack)) {
                    $this->argsByMethodNameStack[$cacheKey] = $namesAndArgs;
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

        if ($node->name instanceof Name && $node->name->toCodeString() === 'view') {
            array_pop($this->argsByMethodNameStack);
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Blade;

use PhpParser\NodeTraverser;
use TomasVotruba\Bladestan\PHPParser\NodeVisitor\BladeLineNumberNodeVisitor;
use TomasVotruba\Bladestan\PHPParser\SimplePhpParser;

final class PhpLineToTemplateLineResolver
{
    public function __construct(
        private readonly BladeLineNumberNodeVisitor $bladeLineNumberNodeVisitor,
        private readonly SimplePhpParser $simplePhpParser,
    ) {
    }

    /**
     * @return array<int, array<string, int>>
     */
    public function resolve(string $phpFileContents): array
    {
        $stmts = $this->simplePhpParser->parse($phpFileContents);
        if ($stmts === []) {
            return [];
        }

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->bladeLineNumberNodeVisitor);
        $nodeTraverser->traverse($stmts);

        return $this->bladeLineNumberNodeVisitor->getPhpLineToBladeTemplateLineMap();
    }
}

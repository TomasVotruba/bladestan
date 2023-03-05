<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Blade;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use TomasVotruba\Bladestan\PHPParser\NodeVisitor\BladeLineNumberNodeVisitor;

final class PhpLineToTemplateLineResolver
{
    private readonly Parser $parser;

    public function __construct(
        private readonly BladeLineNumberNodeVisitor $bladeLineNumberNodeVisitor
    ) {
        $parserFactory = new ParserFactory();
        $this->parser = $parserFactory->create(ParserFactory::PREFER_PHP7);
    }

    /**
     * @return array<int, array<string, int>>
     */
    public function resolve(string $phpFileContent): array
    {
        $stmts = $this->parser->parse($phpFileContent);
        if ($stmts === []) {
            return [];
        }

        if ($stmts === null) {
            return [];
        }

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->bladeLineNumberNodeVisitor);
        $nodeTraverser->traverse($stmts);

        return $this->bladeLineNumberNodeVisitor->getPhpLineToBladeTemplateLineMap();
    }
}

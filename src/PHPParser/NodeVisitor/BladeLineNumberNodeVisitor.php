<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\PHPParser\NodeVisitor;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

final class BladeLineNumberNodeVisitor extends NodeVisitorAbstract
{
    /**
     * Keyed by PHP line number. And inner array has `fileName` => `templateLineNumber`
     *
     * @var array<int, array<string, int>>
     */
    private array $phpLineToBladeTemplateLineMap = [];

    /**
     * @see https://regex101.com/r/GqrJOW/1
     */
    private const TEMPLATE_FILE_NAME_AND_LINE_NUMBER_REGEX = '#/\*\* file: (.*?), line: (\d+) \*/#';

    /**
     * @param Stmt[] $nodes
     *
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes): array
    {
        $this->phpLineToBladeTemplateLineMap = [];

        return $nodes;
    }

    public function enterNode(Node $node)
    {
        $docComment = $node->getDocComment();

        if (! $docComment instanceof Doc) {
            return null;
        }

        $docCommentText = $docComment->getText();

        $matches = [];

        if (! preg_match(self::TEMPLATE_FILE_NAME_AND_LINE_NUMBER_REGEX, $docCommentText, $matches)) {
            return null;
        }

        /** @var string[] $matches */ //@phpcs:ignore
        $this->phpLineToBladeTemplateLineMap[$node->getStartLine()][$matches[1]] = intval($matches[2]);

        return null;
    }

    /**
     * @return array<int, array<string, int>>
     */
    public function getPhpLineToBladeTemplateLineMap(): array
    {
        return $this->phpLineToBladeTemplateLineMap;
    }
}

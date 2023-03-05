<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\PHPParser;

use PhpParser\Node\Stmt;
use PhpParser\Parser;
use PhpParser\ParserFactory;

final class SimplePhpParser
{
    private readonly Parser $nativePhpParser;

    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->nativePhpParser = $parserFactory->create(ParserFactory::ONLY_PHP7);
    }

    /**
     * @return Stmt[]
     */
    public function parse(string $fileContents): array
    {
        $stmts = $this->nativePhpParser->parse($fileContents);
        if ($stmts === null) {
            return [];
        }

        return $stmts;
    }
}

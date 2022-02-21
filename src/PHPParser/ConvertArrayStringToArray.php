<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\PHPParser;

use PhpParser\ConstExprEvaluationException;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

use function assert;
use function count;
use function is_string;

/**
 * This class converts the string `['foo' => 'bar', 'bar' => 'baz']` to actual PHP array `['foo' => 'bar', 'bar' => 'baz']`
 */
final class ConvertArrayStringToArray
{
    private Parser $parser;

    public function __construct(private Standard $printer, private ConstExprEvaluator $constExprEvaluator)
    {
        $parserFactory = new ParserFactory();
        $this->parser  = $parserFactory->create(ParserFactory::ONLY_PHP7);
    }

    /** @return array<string, string> */
    public function convert(string $array): array
    {
        $array = '<?php ' . $array . ';';

        $stmts = $this->parser->parse($array);

        if ($stmts === null || count($stmts) !== 1) {
            return [];
        }

        if (! $stmts[0] instanceof Expression) {
            return [];
        }

        if (! $stmts[0]->expr instanceof Expr\Array_) {
            return [];
        }

        $array = $stmts[0]->expr;
        assert($array instanceof Expr\Array_);

        $result = [];

        foreach ($array->items as $item) {
            assert($item instanceof Expr\ArrayItem);

            if ($item->key === null) {
                continue;
            }

            $key = $this->resolveKey($item->key);

            if (! is_string($key)) {
                continue;
            }

            $value = $this->printer->prettyPrintExpr($item->value);

            $result[$key] = $value;
        }

        return $result;
    }

    private function resolveKey(Expr $expr): mixed
    {
        try {
            return $this->constExprEvaluator->evaluateDirectly($expr);
        } catch (ConstExprEvaluationException) {
            return null;
        }
    }
}

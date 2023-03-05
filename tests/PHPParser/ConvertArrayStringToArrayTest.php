<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\PHPParser;

use Iterator;
use PhpParser\ConstExprEvaluator;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TomasVotruba\Bladestan\PHPParser\ConvertArrayStringToArray;

final class ConvertArrayStringToArrayTest extends TestCase
{
    /**
     * @param array<string, mixed> $expected
     */
    #[DataProvider('greenProvider')]
    #[DataProvider('redProvider')]
    public function testConvertArrayLikeStringToPhpArray(string $array, array $expected): void
    {
        $convertArrayStringToArray = new ConvertArrayStringToArray(new Standard(), new ConstExprEvaluator());

        $this->assertSame($expected, $convertArrayStringToArray->convert($array));
    }

    public static function greenProvider(): Iterator
    {
        yield [
            "['foo' => 'bar', 'bar' => 'baz,bax']", [
                'foo' => "'bar'",
                'bar' => "'baz,bax'",
            ]];
        yield [
            "['foo' => \$foo . 'bar']", [
                'foo' => "\$foo . 'bar'",
            ]];
        yield [
            "['foo' => \$foo->someMethod()]", [
                'foo' => '$foo->someMethod()',
            ]];
    }

    public static function redProvider(): Iterator
    {
        yield ['', []];
        yield ['123', []];
        yield ["'foo'", []];
        yield ['[]', []];
        yield ['[10]', []];
        yield ["['foo', 123]", []];
        yield ["[\$foo => 'bar']", []];
    }
}

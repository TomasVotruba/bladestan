<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\PHPParser;

use PHPUnit\Framework\Attributes\DataProvider;
use Iterator;
use PhpParser\ConstExprEvaluator;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;
use TomasVotruba\Bladestan\PHPParser\ConvertArrayStringToArray;

final class ConvertArrayStringToArrayTest extends TestCase
{
    /**
     * @param array<string, mixed> $expected
     */
    #[DataProvider('greenProvider')]
    #[DataProvider('redProvider')]
    public function test_it_can_convert_array_like_string_to_php_array(string $array, array $expected): void
    {
        $converter = new ConvertArrayStringToArray(new Standard(), new ConstExprEvaluator());

        $this->assertSame($expected, $converter->convert($array));
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

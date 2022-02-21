<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Tests\PHPParser;

use Generator;
use PhpParser\ConstExprEvaluator;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;
use Vural\PHPStanBladeRule\PHPParser\ConvertArrayStringToArray;

/** @covers \Vural\PHPStanBladeRule\PHPParser\ConvertArrayStringToArray */
class ConvertArrayStringToArrayTest extends TestCase
{
    /**
     * @param array<string, mixed> $expected
     *
     * @test
     * @dataProvider greenProvider
     * @dataProvider redProvider
     */
    function it_can_convert_array_like_string_to_php_array(string $array, array $expected): void
    {
        $converter = new ConvertArrayStringToArray(new Standard(), new ConstExprEvaluator());

        $this->assertSame($expected, $converter->convert($array));
    }

    public function greenProvider(): Generator
    {
        yield ["['foo' => 'bar', 'bar' => 'baz,bax']", ['foo' => "'bar'", 'bar' => "'baz,bax'"]];
        yield ["['foo' => \$foo . 'bar']", ['foo' => "\$foo . 'bar'"]];
        yield ["['foo' => \$foo->someMethod()]", ['foo' => '$foo->someMethod()']];
    }

    public function redProvider(): Generator
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

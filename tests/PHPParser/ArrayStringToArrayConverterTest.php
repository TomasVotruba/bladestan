<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\PHPParser;

use Iterator;
use PHPStan\Testing\PHPStanTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use TomasVotruba\Bladestan\PHPParser\ArrayStringToArrayConverter;

final class ArrayStringToArrayConverterTest extends PHPStanTestCase
{
    private ArrayStringToArrayConverter $convertArrayStringToArray;

    protected function setUp(): void
    {
        parent::setUp();

        $this->convertArrayStringToArray = self::getContainer()->getByType(ArrayStringToArrayConverter::class);
    }

    /**
     * @param array<string, mixed> $expectedArray
     */
    #[DataProvider('provideData')]
    public function testConvertArrayLikeStringToPhpArray(string $arrayString, array $expectedArray): void
    {
        $convertedArray = $this->convertArrayStringToArray->convert($arrayString);

        $this->assertSame($expectedArray, $convertedArray);
    }

    public static function provideData(): Iterator
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

        yield ['', []];
        yield ['123', []];
        yield ["'foo'", []];
        yield ['[]', []];
        yield ['[10]', []];
        yield ["['foo', 123]", []];
        yield ["[\$foo => 'bar']", []];
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../../config/extension.neon',
        ];
    }
}

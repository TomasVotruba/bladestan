<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Compiler\BladeToPHPCompiler;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use TomasVotruba\Bladestan\Compiler\BladeToPHPCompiler;
use TomasVotruba\Bladestan\TemplateCompiler\ValueObject\VariableAndType;
use TomasVotruba\Bladestan\Tests\AbstractTestCase;
use TomasVotruba\Bladestan\Tests\TestUtils;

final class BladeToPHPCompilerTest extends AbstractTestCase
{
    /**
     * @var VariableAndType[]
     */
    private array $variables = [];

    private BladeToPHPCompiler $bladeToPHPCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bladeToPHPCompiler = $this->getService(BladeToPHPCompiler::class);

        // Setup the variable names and types that'll be available to all templates
        $this->variables = [];
    }

    #[DataProvider('fixtureProvider')]
    public function testCompileAndDecorateTypes(string $filePath): void
    {
        [$inputBladeContents, $expectedPhpContents] = TestUtils::splitFixture($filePath);

        $phpFileContentsWithLineMap = $this->bladeToPHPCompiler->compileContent('foo.blade.php', $inputBladeContents, $this->variables);

        $this->assertSame($expectedPhpContents, $phpFileContentsWithLineMap->getPhpFileContents());
    }

    public static function fixtureProvider(): Iterator
    {
        /** @var string[] $filePaths */
        $filePaths = glob(__DIR__ . '/Fixture/*');

        foreach ($filePaths as $filePath) {
            yield [$filePath];
        }
    }
}

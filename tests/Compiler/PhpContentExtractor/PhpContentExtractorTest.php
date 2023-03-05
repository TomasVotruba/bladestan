<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Compiler\PhpContentExtractor;

use Illuminate\View\Compilers\BladeCompiler;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use TomasVotruba\Bladestan\Compiler\FileNameAndLineNumberAddingPreCompiler;
use TomasVotruba\Bladestan\Compiler\PhpContentExtractor;
use TomasVotruba\Bladestan\Tests\AbstractTestCase;
use TomasVotruba\Bladestan\Tests\TestUtils;

final class PhpContentExtractorTest extends AbstractTestCase
{
    private PhpContentExtractor $phpContentExtractor;

    private BladeCompiler $bladeCompiler;

    private FileNameAndLineNumberAddingPreCompiler $fileNameAndLineNumberAddingPreCompiler;

    protected function setUp(): void
    {
        $this->templatePaths = ['resources/views'];

        parent::setUp();

        $this->phpContentExtractor = $this->getService(PhpContentExtractor::class);
        $this->bladeCompiler = $this->getService(BladeCompiler::class);
        $this->fileNameAndLineNumberAddingPreCompiler = $this->getService(FileNameAndLineNumberAddingPreCompiler::class);
    }

    #[DataProvider('fixtureProvider')]
    public function testExtactPhpContentsFromBladeTemlate(string $filePath): void
    {
        [$inputBladeContents, $expectedPhpContents] = TestUtils::splitFixture($filePath);

        $fileContent = $this->fileNameAndLineNumberAddingPreCompiler
            ->setFileName('/some-directory-name/resources/views/foo.blade.php')
            ->compileString($inputBladeContents);

        $compiledPhpContents = $this->bladeCompiler->compileString($fileContent);
        $phpFileContent = $this->phpContentExtractor->extract($compiledPhpContents);

        $this->assertSame($expectedPhpContents, $phpFileContent);
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

<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Compiler\FileNameAndLineNumberAddingPreCompiler;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use TomasVotruba\Bladestan\Compiler\FileNameAndLineNumberAddingPreCompiler;
use TomasVotruba\Bladestan\Configuration\Configuration;
use TomasVotruba\Bladestan\Tests\AbstractTestCase;
use TomasVotruba\Bladestan\Tests\TestUtils;

final class FileNameAndLineNumberAddingPreCompilerTest extends AbstractTestCase
{
    private FileNameAndLineNumberAddingPreCompiler $fileNameAndLineNumberAddingPreCompiler;

    protected function setUp(): void
    {
        $this->templatePaths = ['resources/views'];

        parent::setUp();

        $this->fileNameAndLineNumberAddingPreCompiler = $this->getService(FileNameAndLineNumberAddingPreCompiler::class);
    }

    #[DataProvider('fixtureProvider')]
    public function testUpdateLineNumbers(string $filePath): void
    {
        $this->fileNameAndLineNumberAddingPreCompiler->setFileName('/var/www/resources/views/foo.blade.php');

        [$inputBladeContents, $expectedPhpCompiledContent] = TestUtils::splitFixture($filePath);

        $phpFileContent = $this->fileNameAndLineNumberAddingPreCompiler->compileString($inputBladeContents);
        $this->assertSame($expectedPhpCompiledContent, $phpFileContent);
    }

    public static function fixtureProvider(): Iterator
    {
        /** @var string[] $filePaths */
        $filePaths = glob(__DIR__ . '/Fixture/*');

        foreach ($filePaths as $filePath) {
            yield [$filePath];
        }
    }

    public function testChangeFileForSameTemplate(): void
    {
        $this->fileNameAndLineNumberAddingPreCompiler->setFileName('/var/www/resources/views/foo.blade.php');

        $this->assertSame(
            '/** file: foo.blade.php, line: 1 */{{ $foo }}',
            $this->fileNameAndLineNumberAddingPreCompiler->compileString('{{ $foo }}')
        );

        $this->fileNameAndLineNumberAddingPreCompiler->setFileName('/var/www/resources/views/bar.blade.php');

        $this->assertSame(
            '/** file: bar.blade.php, line: 1 */{{ $foo }}',
            $this->fileNameAndLineNumberAddingPreCompiler->compileString('{{ $foo }}')
        );
    }

    public function testShowTemplateDirectory(): void
    {
        $this->fileNameAndLineNumberAddingPreCompiler->setFileName('/var/www/resources/views/users/index.blade.php');

        $this->assertSame(
            '/** file: users/index.blade.php, line: 1 */{{ $foo }}',
            $this->fileNameAndLineNumberAddingPreCompiler->compileString('{{ $foo }}')
        );
    }

    public function testFindCorrectTemplatePath(): void
    {
        $configuration = new Configuration([
            Configuration::TEMPLATE_PATHS => [
                'resources/views',
                'foo/bar',
            ],
        ]);

        $fileNameAndLineNumberAddingPreCompiler = new FileNameAndLineNumberAddingPreCompiler($configuration);
        $fileNameAndLineNumberAddingPreCompiler->setFileName('/var/www/foo/bar/users/index.blade.php');

        $this->assertSame(
            '/** file: users/index.blade.php, line: 1 */{{ $foo }}',
            $fileNameAndLineNumberAddingPreCompiler->compileString('{{ $foo }}')
        );
    }
}

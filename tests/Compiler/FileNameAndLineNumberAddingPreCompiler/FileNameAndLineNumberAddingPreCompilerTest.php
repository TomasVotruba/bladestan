<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Compiler\FileNameAndLineNumberAddingPreCompiler;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;
use TomasVotruba\Bladestan\Compiler\FileNameAndLineNumberAddingPreCompiler;
use TomasVotruba\Bladestan\Configuration\Configuration;
use TomasVotruba\Bladestan\Tests\AbstractTestCase;

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
    public function testUpdateLineNumbers(SmartFileInfo $fileInfo): void
    {
        $this->fileNameAndLineNumberAddingPreCompiler->setFileName('/var/www/resources/views/foo.blade.php');

        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToInputAndExpected($fileInfo);
        $phpFileContent = $this->fileNameAndLineNumberAddingPreCompiler->compileString(trim($inputAndExpected->getInput()));

        StaticFixtureUpdater::updateFixtureContent($inputAndExpected->getInput(), $phpFileContent, $fileInfo);

        $this->assertSame(trim((string) $inputAndExpected->getExpected()), $phpFileContent);
    }

    public static function fixtureProvider(): Iterator
    {
        return StaticFixtureFinder::yieldDirectoryExclusively(__DIR__ . '/Fixture', '*.blade.php');
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

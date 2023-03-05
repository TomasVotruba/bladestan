<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Compiler;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;
use TomasVotruba\Bladestan\Compiler\FileNameAndLineNumberAddingPreCompiler;
use TomasVotruba\Bladestan\Compiler\PhpContentExtractor;

final class PhpContentExtractorTest extends TestCase
{
    private PhpContentExtractor $phpContentExtractor;

    private BladeCompiler $bladeCompiler;

    private FileNameAndLineNumberAddingPreCompiler $fileNameAndLineNumberAddingPreCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phpContentExtractor = new PhpContentExtractor();
        $this->bladeCompiler = new BladeCompiler(new Filesystem(), sys_get_temp_dir());
        $this->fileNameAndLineNumberAddingPreCompiler = new FileNameAndLineNumberAddingPreCompiler(['resources/views']);
    }

    #[DataProvider('fixtureProvider')]
    public function test_it_can_extract_php_contents_from_compiled_blade_template_string(SmartFileInfo $fileInfo): void
    {
        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToInputAndExpected($fileInfo);
        $input = $this->compile($inputAndExpected->getInput());
        $phpFileContent = $this->phpContentExtractor->extract($input);

        StaticFixtureUpdater::updateFixtureContent($inputAndExpected->getInput(), $phpFileContent, $fileInfo);

        $this->assertSame(trim((string) $inputAndExpected->getExpected()), trim($phpFileContent));
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public static function fixtureProvider(): Iterator
    {
        return StaticFixtureFinder::yieldDirectoryExclusively(__DIR__ . '/Fixture/PhpContentExtractor', '*.blade.php');
    }

    private function compile(string $bladeTemplate): string
    {
        $fileContent = $this->fileNameAndLineNumberAddingPreCompiler->setFileName('/foo/resources/views/foo.blade.php')->compileString(trim($bladeTemplate));

        return $this->bladeCompiler->compileString($fileContent);
    }
}

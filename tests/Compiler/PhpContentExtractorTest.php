<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Tests\Compiler;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Iterator;
use PHPUnit\Framework\TestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;
use Vural\PHPStanBladeRule\Compiler\FileNameAndLineNumberAddingPreCompiler;
use Vural\PHPStanBladeRule\Compiler\PhpContentExtractor;

use function sys_get_temp_dir;
use function trim;

/** @covers \Vural\PHPStanBladeRule\Compiler\PhpContentExtractor */
class PhpContentExtractorTest extends TestCase
{
    private PhpContentExtractor $extractor;
    private BladeCompiler $compiler;
    private FileNameAndLineNumberAddingPreCompiler $preCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor   = new PhpContentExtractor();
        $this->compiler    = new BladeCompiler(new Filesystem(), sys_get_temp_dir());
        $this->preCompiler = new FileNameAndLineNumberAddingPreCompiler(['resources/views']);
    }

    /**
     * @test
     * @dataProvider fixtureProvider
     */
    function it_can_extract_php_contents_from_compiled_blade_template_string(SmartFileInfo $fileInfo): void
    {
        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToInputAndExpected($fileInfo);
        $input            = $this->compile($inputAndExpected->getInput());
        $phpFileContent   = $this->extractor->extract($input);

        StaticFixtureUpdater::updateFixtureContent($inputAndExpected->getInput(), $phpFileContent, $fileInfo);

        $this->assertSame(trim($inputAndExpected->getExpected()), trim($phpFileContent));
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function fixtureProvider(): Iterator
    {
        return StaticFixtureFinder::yieldDirectoryExclusively(__DIR__ . '/Fixture/PhpContentExtractor', '*.blade.php');
    }

    private function compile(string $bladeTemplate): string
    {
        $fileContent = $this->preCompiler->setFileName('/foo/resources/views/foo.blade.php')->compileString(trim($bladeTemplate));

        return $this->compiler->compileString($fileContent);
    }
}

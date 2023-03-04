<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Compiler;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Iterator;
use PHPUnit\Framework\TestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;
use TomasVotruba\Bladestan\Compiler\FileNameAndLineNumberAddingPreCompiler;
use TomasVotruba\Bladestan\Compiler\PhpContentExtractor;

use function sys_get_temp_dir;
use function trim;

#[\PHPUnit\Framework\Attributes\CoversClass(\TomasVotruba\Bladestan\Compiler\PhpContentExtractor::class)]
class PhpContentExtractorTest extends TestCase
{
    private PhpContentExtractor $extractor;

    private BladeCompiler $compiler;

    private FileNameAndLineNumberAddingPreCompiler $preCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extractor = new PhpContentExtractor();
        $this->compiler = new BladeCompiler(new Filesystem(), sys_get_temp_dir());
        $this->preCompiler = new FileNameAndLineNumberAddingPreCompiler(['resources/views']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('fixtureProvider')]
    public function test_it_can_extract_php_contents_from_compiled_blade_template_string(SmartFileInfo $fileInfo): void
    {
        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToInputAndExpected($fileInfo);
        $input = $this->compile($inputAndExpected->getInput());
        $phpFileContent = $this->extractor->extract($input);

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
        $fileContent = $this->preCompiler->setFileName('/foo/resources/views/foo.blade.php')->compileString(trim($bladeTemplate));

        return $this->compiler->compileString($fileContent);
    }
}

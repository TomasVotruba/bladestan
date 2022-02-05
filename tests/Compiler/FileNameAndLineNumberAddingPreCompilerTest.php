<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Tests\Compiler;

use Iterator;
use PHPUnit\Framework\TestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;
use Vural\PHPStanBladeRule\Compiler\FileNameAndLineNumberAddingPreCompiler;

use function trim;

/** @covers \Vural\PHPStanBladeRule\Compiler\FileNameAndLineNumberAddingPreCompiler */
class FileNameAndLineNumberAddingPreCompilerTest extends TestCase
{
    private FileNameAndLineNumberAddingPreCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new FileNameAndLineNumberAddingPreCompiler(['resources/views']);
    }

    /**
     * @test
     * @dataProvider fixtureProvider
     */
    function it_can_add_line_numbers_to_blade_content(SmartFileInfo $fileInfo): void
    {
        $this->compiler->setFileName('/var/www/resources/views/foo.blade.php');

        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToInputAndExpected($fileInfo);
        $phpFileContent   = $this->compiler->compileString(trim($inputAndExpected->getInput()));

        StaticFixtureUpdater::updateFixtureContent($inputAndExpected->getInput(), $phpFileContent, $fileInfo);

        $this->assertSame(trim($inputAndExpected->getExpected()), $phpFileContent);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function fixtureProvider(): Iterator
    {
        return StaticFixtureFinder::yieldDirectoryExclusively(__DIR__ . '/Fixture/FileNameAndLineNumberAddingPreCompiler', '*.blade.php');
    }

    /** @test */
    function it_can_change_file_name_for_same_template(): void
    {
        $this->compiler->setFileName('/var/www/resources/views/foo.blade.php');

        $this->assertSame(
            '/** file: foo.blade.php, line: 1 */{{ $foo }}',
            $this->compiler->compileString('{{ $foo }}')
        );

        $this->compiler->setFileName('/var/www/resources/views/bar.blade.php');

        $this->assertSame(
            '/** file: bar.blade.php, line: 1 */{{ $foo }}',
            $this->compiler->compileString('{{ $foo }}')
        );
    }

    /** @test */
    function it_shows_the_template_directory(): void
    {
        $this->compiler->setFileName('/var/www/resources/views/users/index.blade.php');

        $this->assertSame(
            '/** file: users/index.blade.php, line: 1 */{{ $foo }}',
            $this->compiler->compileString('{{ $foo }}')
        );
    }

    /** @test */
    function it_will_loop_over_template_paths_to_find_correct_one(): void
    {
        $compiler = new FileNameAndLineNumberAddingPreCompiler([
            'resources/views',
            'foo/bar',
        ]);

        $compiler->setFileName('/var/www/foo/bar/users/index.blade.php');

        $this->assertSame(
            '/** file: users/index.blade.php, line: 1 */{{ $foo }}',
            $compiler->compileString('{{ $foo }}')
        );
    }
}

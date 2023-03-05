<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Compiler;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;
use TomasVotruba\Bladestan\Compiler\FileNameAndLineNumberAddingPreCompiler;

final class FileNameAndLineNumberAddingPreCompilerTest extends TestCase
{
    private FileNameAndLineNumberAddingPreCompiler $fileNameAndLineNumberAddingPreCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileNameAndLineNumberAddingPreCompiler = new FileNameAndLineNumberAddingPreCompiler(['resources/views']);
    }

    #[DataProvider('fixtureProvider')]
    public function test_it_can_add_line_numbers_to_blade_content(SmartFileInfo $fileInfo): void
    {
        $this->fileNameAndLineNumberAddingPreCompiler->setFileName('/var/www/resources/views/foo.blade.php');

        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToInputAndExpected($fileInfo);
        $phpFileContent = $this->fileNameAndLineNumberAddingPreCompiler->compileString(trim($inputAndExpected->getInput()));

        StaticFixtureUpdater::updateFixtureContent($inputAndExpected->getInput(), $phpFileContent, $fileInfo);

        $this->assertSame(trim((string) $inputAndExpected->getExpected()), $phpFileContent);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public static function fixtureProvider(): Iterator
    {
        return StaticFixtureFinder::yieldDirectoryExclusively(__DIR__ . '/Fixture/FileNameAndLineNumberAddingPreCompiler', '*.blade.php');
    }

    public function test_it_can_change_file_name_for_same_template(): void
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

    public function test_it_shows_the_template_directory(): void
    {
        $this->fileNameAndLineNumberAddingPreCompiler->setFileName('/var/www/resources/views/users/index.blade.php');

        $this->assertSame(
            '/** file: users/index.blade.php, line: 1 */{{ $foo }}',
            $this->fileNameAndLineNumberAddingPreCompiler->compileString('{{ $foo }}')
        );
    }

    public function test_it_will_loop_over_template_paths_to_find_correct_one(): void
    {
        $fileNameAndLineNumberAddingPreCompiler = new FileNameAndLineNumberAddingPreCompiler([
            'resources/views',
            'foo/bar',
        ]);

        $fileNameAndLineNumberAddingPreCompiler->setFileName('/var/www/foo/bar/users/index.blade.php');

        $this->assertSame(
            '/** file: users/index.blade.php, line: 1 */{{ $foo }}',
            $fileNameAndLineNumberAddingPreCompiler->compileString('{{ $foo }}')
        );
    }
}

<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Tests\Compiler;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\FileViewFinder;
use Iterator;
use PhpParser\ConstExprEvaluator;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\TemplatePHPStanCompiler\NodeFactory\VarDocNodeFactory;
use Symplify\TemplatePHPStanCompiler\ValueObject\VariableAndType;
use Vural\PHPStanBladeRule\Blade\PhpLineToTemplateLineResolver;
use Vural\PHPStanBladeRule\Compiler\BladeToPHPCompiler;
use Vural\PHPStanBladeRule\Compiler\FileNameAndLineNumberAddingPreCompiler;
use Vural\PHPStanBladeRule\Compiler\PhpContentExtractor;
use Vural\PHPStanBladeRule\PHPParser\ConvertArrayStringToArray;
use Vural\PHPStanBladeRule\PHPParser\NodeVisitor\BladeLineNumberNodeVisitor;

use function sys_get_temp_dir;
use function trim;

/** @covers \Vural\PHPStanBladeRule\Compiler\BladeToPHPCompiler */
class BladeToPHPCompilerTest extends TestCase
{
    /** @var VariableAndType[] */
    private array $variables;

    private BladeToPHPCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $templatePaths = [__DIR__ . '/Fixture/BladeToPHPCompiler'];

        $this->compiler = new BladeToPHPCompiler(
            $fileSystem = new Filesystem(),
            new BladeCompiler($fileSystem, sys_get_temp_dir()),
            new Standard(),
            new VarDocNodeFactory(),
            new FileViewFinder($fileSystem, $templatePaths),
            new FileNameAndLineNumberAddingPreCompiler($templatePaths),
            new PhpLineToTemplateLineResolver(new BladeLineNumberNodeVisitor()),
            new PhpContentExtractor(),
            new ConvertArrayStringToArray(new Standard(), new ConstExprEvaluator()),
        );

        // Setup the variable names and types that'll be available to all templates
        $this->variables = [];
    }

    /**
     * @test
     * @dataProvider fixtureProvider
     */
    function it_can_compile_and_decorate_blade_template(SmartFileInfo $fileInfo): void
    {
        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToInputAndExpected($fileInfo);
        $phpFileContent   = $this->compiler->compileContent('foo.blade.php', $inputAndExpected->getInput(), $this->variables);

        StaticFixtureUpdater::updateFixtureContent($inputAndExpected->getInput(), $phpFileContent->getPhpFileContents(), $fileInfo);

        $this->assertSame(trim($inputAndExpected->getExpected()), $phpFileContent->getPhpFileContents());
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function fixtureProvider(): Iterator
    {
        return StaticFixtureFinder::yieldDirectoryExclusively(__DIR__ . '/Fixture/BladeToPHPCompiler', '*.blade.php');
    }
}

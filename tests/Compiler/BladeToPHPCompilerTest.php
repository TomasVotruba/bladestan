<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Compiler;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\FileViewFinder;
use Iterator;
use PhpParser\ConstExprEvaluator;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;
use TomasVotruba\Bladestan\Blade\PhpLineToTemplateLineResolver;
use TomasVotruba\Bladestan\Compiler\BladeToPHPCompiler;
use TomasVotruba\Bladestan\Compiler\FileNameAndLineNumberAddingPreCompiler;
use TomasVotruba\Bladestan\Compiler\PhpContentExtractor;
use TomasVotruba\Bladestan\PHPParser\ConvertArrayStringToArray;
use TomasVotruba\Bladestan\PHPParser\NodeVisitor\BladeLineNumberNodeVisitor;
use TomasVotruba\Bladestan\TemplateCompiler\NodeFactory\VarDocNodeFactory;
use TomasVotruba\Bladestan\TemplateCompiler\ValueObject\VariableAndType;

final class BladeToPHPCompilerTest extends TestCase
{
    /**
     * @var VariableAndType[]
     */
    private array $variables = [];

    private BladeToPHPCompiler $bladeToPHPCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $templatePaths = [__DIR__ . '/Fixture/BladeToPHPCompiler'];

        $this->bladeToPHPCompiler = new BladeToPHPCompiler(
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

    #[DataProvider('fixtureProvider')]
    public function test_it_can_compile_and_decorate_blade_template(SmartFileInfo $fileInfo): void
    {
        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToInputAndExpected($fileInfo);
        $phpFileContentsWithLineMap = $this->bladeToPHPCompiler->compileContent('foo.blade.php', $inputAndExpected->getInput(), $this->variables);

        StaticFixtureUpdater::updateFixtureContent($inputAndExpected->getInput(), $phpFileContentsWithLineMap->getPhpFileContents(), $fileInfo);

        $this->assertSame(trim((string) $inputAndExpected->getExpected()), $phpFileContentsWithLineMap->getPhpFileContents());
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public static function fixtureProvider(): Iterator
    {
        return StaticFixtureFinder::yieldDirectoryExclusively(__DIR__ . '/Fixture/BladeToPHPCompiler', '*.blade.php');
    }
}

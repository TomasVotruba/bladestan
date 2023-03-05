<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Compiler;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\FileViewFinder;
use Iterator;
use PhpParser\ConstExprEvaluator;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use TomasVotruba\Bladestan\Blade\PhpLineToTemplateLineResolver;
use TomasVotruba\Bladestan\Compiler\BladeToPHPCompiler;
use TomasVotruba\Bladestan\Compiler\FileNameAndLineNumberAddingPreCompiler;
use TomasVotruba\Bladestan\Compiler\PhpContentExtractor;
use TomasVotruba\Bladestan\PHPParser\ConvertArrayStringToArray;
use TomasVotruba\Bladestan\PHPParser\NodeVisitor\BladeLineNumberNodeVisitor;
use TomasVotruba\Bladestan\TemplateCompiler\NodeFactory\VarDocNodeFactory;
use TomasVotruba\Bladestan\TemplateCompiler\ValueObject\VariableAndType;
use TomasVotruba\Bladestan\Tests\TestUtils;

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

        $container = new Container();
        $container->singleton(BladeCompiler::class, function (): BladeCompiler {
            return new BladeCompiler(new Filesystem(), sys_get_temp_dir());
        });

        $templatePaths = [__DIR__ . '/Fixture/BladeToPHPCompiler'];
        $container->singleton(FileViewFinder::class, function () use ($templatePaths) {
            return new FileViewFinder(new Filesystem(), $templatePaths);
        });

        $this->bladeToPHPCompiler = $container->make(BladeToPHPCompiler::class);

        //// @todo this should be handled by container
        //$this->bladeToPHPCompiler = new BladeToPHPCompiler(
        //    $fileSystem = new Filesystem(),
        //    new BladeCompiler($fileSystem, sys_get_temp_dir()),
        //    new Standard(),
        //    new VarDocNodeFactory(),
        //    new FileViewFinder($fileSystem, $templatePaths),
        //    new FileNameAndLineNumberAddingPreCompiler($templatePaths),
        //    new PhpLineToTemplateLineResolver(new BladeLineNumberNodeVisitor()),
        //    new PhpContentExtractor(),
        //    new ConvertArrayStringToArray(new Standard(), new ConstExprEvaluator()),
        //);

        // Setup the variable names and types that'll be available to all templates
        $this->variables = [];
    }

    #[DataProvider('fixtureProvider')]
    public function test_it_can_compile_and_decorate_blade_template(string $filePath): void
    {
        [$inputBladeContents, $expectedPhpContents] = TestUtils::splitFixture($filePath);

        $phpFileContentsWithLineMap = $this->bladeToPHPCompiler->compileContent('foo.blade.php', $inputBladeContents, $this->variables);

        //StaticFixtureUpdater::updateFixtureContent($inputAndExpected->getInput(), $phpFileContentsWithLineMap->getPhpFileContents(), $fileInfo);

        $this->assertSame($expectedPhpContents, $phpFileContentsWithLineMap);
    }

    public static function fixtureProvider(): Iterator
    {
        /** @var string[] $filePaths */
        $filePaths = glob(__DIR__ . '/Fixture/BladeToPHPCompiler/*');

        foreach ($filePaths as $filePath) {
            yield [$filePath];
        }
    }
}

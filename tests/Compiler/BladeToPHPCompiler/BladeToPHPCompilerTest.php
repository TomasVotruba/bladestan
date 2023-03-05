<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Compiler\BladeToPHPCompiler;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\FileViewFinder;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TomasVotruba\Bladestan\Compiler\BladeToPHPCompiler;
use TomasVotruba\Bladestan\Configuration\Configuration;
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

        // @todo extract to parent factory
        $container = new Container();
        $container->singleton(BladeCompiler::class, function (): BladeCompiler {
            return new BladeCompiler(new Filesystem(), sys_get_temp_dir());
        });

        $templatePaths = [__DIR__ . '/Fixture'];

        $container->singleton(FileViewFinder::class, function () use ($templatePaths) {
            return new FileViewFinder(new Filesystem(), $templatePaths);
        });

        $container->singleton(Configuration::class, function () use ($templatePaths) {
            return new Configuration([
                Configuration::TEMPLATE_PATHS => $templatePaths,
            ]);
        });

        $this->bladeToPHPCompiler = $container->make(BladeToPHPCompiler::class);

        // Setup the variable names and types that'll be available to all templates
        $this->variables = [];
    }

    #[DataProvider('fixtureProvider')]
    public function testCompileAndDecorateTypes(string $filePath): void
    {
        [$inputBladeContents, $expectedPhpContents] = TestUtils::splitFixture($filePath);

        $phpFileContentsWithLineMap = $this->bladeToPHPCompiler->compileContent('foo.blade.php', $inputBladeContents, $this->variables);

        //StaticFixtureUpdater::updateFixtureContent($inputAndExpected->getInput(), $phpFileContentsWithLineMap->getPhpFileContents(), $fileInfo);

        $this->assertSame($expectedPhpContents, $phpFileContentsWithLineMap->getPhpFileContents());
    }

    public static function fixtureProvider(): Iterator
    {
        /** @var string[] $filePaths */
        $filePaths = glob(__DIR__ . '/Fixture/*');

        foreach ($filePaths as $filePath) {
            yield [$filePath];
        }
    }
}

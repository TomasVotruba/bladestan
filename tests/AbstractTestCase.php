<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\FileViewFinder;
use PHPUnit\Framework\TestCase;
use TomasVotruba\Bladestan\Configuration\Configuration;

abstract class AbstractTestCase extends TestCase
{
    private Container $container;

    /**
     * Allow override to adjust container configuration
     * @var string[]
     */
    protected array $templatePaths = [__DIR__ . '/Fixture'];

    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(BladeCompiler::class, static fn (): BladeCompiler => new BladeCompiler(new Filesystem(), sys_get_temp_dir()));

        // by convention to avoid rebuilding docs container and over again
        $templatePaths = $this->templatePaths;

        $container->singleton(FileViewFinder::class, static fn (): FileViewFinder => new FileViewFinder(new Filesystem(), $templatePaths));

        $container->singleton(Configuration::class, static fn (): Configuration => new Configuration([
            Configuration::TEMPLATE_PATHS => $templatePaths,
        ]));

        $this->container = $container;
    }

    /**
     * @template TService as object
     *
     * @param class-string<TService> $type
     * @return TService
     */
    public function getService(string $type): object
    {
        return $this->container->make($type);
    }
}

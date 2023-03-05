<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Laravel\View;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\FileViewFinder;
use TomasVotruba\Bladestan\Configuration\Configuration;
use TomasVotruba\Bladestan\Support\DirectoryHelper;

final class FileViewFinderFactory
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly Configuration $configuration,
        private readonly DirectoryHelper $directoryHelper,
    ) {
    }

    public function create(): FileViewFinder
    {
        // @note is the absolute path needed?
        $absoluteTemplatePaths = $this->directoryHelper->absolutizePaths($this->configuration->getTemplatePaths());

        return new FileViewFinder(
            $this->filesystem,
            $absoluteTemplatePaths,
            // @note why SVG?
            ['blade.php', 'svg']
        );
    }
}

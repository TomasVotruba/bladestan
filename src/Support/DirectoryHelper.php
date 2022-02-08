<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Support;

use PHPStan\File\FileHelper;

use function array_map;
use function str_replace;

final class DirectoryHelper
{
    public function __construct(private FileHelper $fileHelper)
    {
    }

    /**
     * @param string[] $paths
     *
     * @return string[]
     */
    public function absolutizePaths(array $paths): array
    {
        return array_map(function (string $path) {
            $path = $this->fileHelper->absolutizePath($path);

            return str_replace(['phar://', 'vendor/phpstan/phpstan/phpstan.phar/'], ['', ''], $path);
        }, $paths);
    }
}

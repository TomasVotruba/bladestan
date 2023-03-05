<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests;

use Webmozart\Assert\Assert;

final class TestUtils
{
    /**
     * @return array{0: string, 1: string}
     */
    public static function splitFixture(string $filePath): array
    {
        Assert::file($filePath);
        $fileContents = file_get_contents($filePath);

        $stringsCollection = str($fileContents)->split('#-----\n#')
            ->values();

        return [
            $stringsCollection[0],
            $stringsCollection[1],
        ];
    }
}

<?php

declare(strict_types=1);
use Rector\Config\RectorConfig;

use Rector\Php80\Rector\FuncCall\TokenGetAllToObjectRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        // configs
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
        __DIR__ . '/easy-ci.php',
    ]);

    $rectorConfig->rules([FinalizeClassesWithoutChildrenRector::class]);

    $rectorConfig->importNames();

    $rectorConfig->skip([
        '*/Fixture/*',
        // buggy for follow up token updates
        TokenGetAllToObjectRector::class,
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        PHPUnitSetList::PHPUNIT_100,
        SetList::CODING_STYLE,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::PRIVATIZATION,
        SetList::NAMING,
        SetList::TYPE_DECLARATION,
    ]);
};

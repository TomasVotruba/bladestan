<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        // configs
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector.php',
        __DIR__ . '/easy-ci.php',
    ]);

    $ecsConfig->skip(['*/Fixture/*']);

    // this way you can add sets - group of rules
    $ecsConfig->sets([
        SetList::SPACES,
        SetList::ARRAY,
        SetList::STRICT,
        SetList::DOCBLOCK,
        SetList::NAMESPACES,
        SetList::COMMENTS,
        SetList::PSR_12,
    ]);

    $ecsConfig->rules([\Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer::class]);
};

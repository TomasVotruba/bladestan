<?php

declare(strict_types=1);

use Symplify\EasyCI\Config\EasyCIConfig;

return static function (EasyCIConfig $easyCIConfig): void {
    $easyCIConfig->typesToSkip([
        // used internally by PHPStan
        \TomasVotruba\Bladestan\ErrorReporting\PHPStan\ErrorFormatter\BladeTemplateErrorFormatter::class,
        \TomasVotruba\Bladestan\PHPParser\NodeVisitor\ViewFunctionArgumentsNodeVisitor::class,

        // service factory used in /config
        \TomasVotruba\Bladestan\Laravel\View\FileViewFinderFactory::class,
        \TomasVotruba\Bladestan\Laravel\View\BladeCompilerFactory::class,
    ]);
};

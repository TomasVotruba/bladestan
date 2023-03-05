<?php

declare(strict_types=1);
use Symplify\EasyCI\Config\EasyCIConfig;
use TomasVotruba\Bladestan\ErrorReporting\PHPStan\ErrorFormatter\BladeTemplateErrorFormatter;
use TomasVotruba\Bladestan\Laravel\View\BladeCompilerFactory;
use TomasVotruba\Bladestan\Laravel\View\FileViewFinderFactory;

use TomasVotruba\Bladestan\PhpParser\NodeVisitor\ViewFunctionArgumentsNodeVisitor;

return static function (EasyCIConfig $easyCIConfig): void {
    $easyCIConfig->typesToSkip([
        // used internally by PHPStan
        BladeTemplateErrorFormatter::class,
        ViewFunctionArgumentsNodeVisitor::class,

        // service factory used in /config
        FileViewFinderFactory::class,
        BladeCompilerFactory::class,
    ]);
};

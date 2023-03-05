<?php

declare(strict_types=1);

use Symplify\EasyCI\Config\EasyCIConfig;

return static function (EasyCIConfig $easyCIConfig): void {
    $easyCIConfig->typesToSkip([
        // remove later, now hacked in the config
        // @todo consider using Laravel container to build the services that convert blade templates to PHP
        \TomasVotruba\Bladestan\Support\DirectoryHelper::class,

        // used internally by PHPStan
        \TomasVotruba\Bladestan\ErrorReporting\PHPStan\ErrorFormatter\BladeTemplateErrorFormatter::class,
        \TomasVotruba\Bladestan\PHPParser\NodeVisitor\ViewFunctionArgumentsNodeVisitor::class,
    ]);
};

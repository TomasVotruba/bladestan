<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\NodeAnalyzer;

use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewName;
use InvalidArgumentException;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;

use function file_exists;
use function is_string;

/** @see \Symplify\TemplatePHPStanCompiler\NodeAnalyzer\TemplateFilePathResolver */
final class TemplateFilePathResolver
{
    public function __construct(
        private FileViewFinder $fileViewFinder,
        private ValueResolver $valueResolver,
    ) {
    }

    /** @return string[] */
    public function resolveExistingFilePaths(Expr $expr, Scope $scope): array
    {
        $resolvedValue = $this->valueResolver->resolve($expr, $scope);

        if (! is_string($resolvedValue)) {
            return [];
        }

        $resolvedValue = $this->normalizeName($resolvedValue);

        if (file_exists($resolvedValue)) {
            return [$resolvedValue];
        }

        $view = $this->findView($resolvedValue);

        if ($view === null) {
            return [];
        }

        return [$view];
    }

    private function normalizeName(string $name): string
    {
        return ViewName::normalize($name);
    }

    private function findView(string $view): ?string
    {
        try {
            return $this->fileViewFinder->find($view);
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}

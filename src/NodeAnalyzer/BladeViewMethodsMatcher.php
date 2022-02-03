<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\NodeAnalyzer;

use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\View\Factory;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Symplify\TemplatePHPStanCompiler\ValueObject\RenderTemplateWithParameters;

use function count;
use function in_array;

final class BladeViewMethodsMatcher
{
    private const MAKE = 'make';

    private const VIEW_FACTORY_METHOD_NAMES = ['make', 'renderWhen', 'renderUnless'];

    public function __construct(
        private TemplateFilePathResolver $templateFilePathResolver,
        private ViewDataParametersAnalyzer $viewDataParametersAnalyzer
    ) {
    }

    /** @return RenderTemplateWithParameters[] */
    public function match(Node\Expr\MethodCall $methodCall, Scope $scope): array
    {
        $methodName = $this->resolveName($methodCall);

        if ($methodName === null) {
            return [];
        }

        $calledOnType = $scope->getType($methodCall->var);

        if ($calledOnType instanceof ThisType) {
            $calledOnType = new ObjectType($calledOnType->getClassName());
        }

        if (! $calledOnType instanceof ObjectType) {
            return [];
        }

        if (! $this->isCalledOnTypeABladeView($calledOnType, $methodName)) {
            return [];
        }

        $templateNameArgument = $this->findTemplateNameArgument($methodName, $methodCall);

        if ($templateNameArgument === null) {
            return [];
        }

        $template = $templateNameArgument->value;

        $resolvedTemplateFilePaths = $this->templateFilePathResolver->resolveExistingFilePaths(
            $template,
            $scope,
        );

        if ($resolvedTemplateFilePaths === []) {
            return [];
        }

        $templateDataArgument = $this->findTemplateDataArgument($methodName, $methodCall);

        if ($templateDataArgument === null) {
            $parametersArray = new Node\Expr\Array_();
        } else {
            $parametersArray = $this->viewDataParametersAnalyzer->resolveParametersArray($templateDataArgument, $scope);
        }

        $result = [];

        foreach ($resolvedTemplateFilePaths as $resolvedTemplateFilePath) {
            $result[] = new RenderTemplateWithParameters($resolvedTemplateFilePath, $parametersArray);
        }

        return $result;
    }

    private function resolveName(Node\Expr\MethodCall $methodCall): ?string
    {
        if (! $methodCall->name instanceof Node\Identifier) {
            return null;
        }

        return $methodCall->name->name;
    }

    private function isCalledOnTypeABladeView(ObjectType $objectType, string $methodName): bool
    {
        if ($objectType->isInstanceOf(Factory::class)->yes()) {
            return in_array($methodName, self::VIEW_FACTORY_METHOD_NAMES, true);
        }

        if ($objectType->isInstanceOf(ViewFactoryContract::class)->yes()) {
            return $methodName === self::MAKE;
        }

        return false;
    }

    private function findTemplateNameArgument(string $methodName, Node\Expr\MethodCall $methodCall): ?Node\Arg
    {
        if (count($methodCall->getArgs()) < 1) {
            return null;
        }

        // `make` just takes view name and data as arguments
        if ($methodName === self::MAKE) {
            return $methodCall->getArgs()[0];
        }

        // Here it can just be `renderWhen` or `renderUnless`
        if (count($methodCall->getArgs()) < 2) {
            return null;
        }

        // Second argument is the template name
        return $methodCall->getArgs()[1];
    }

    private function findTemplateDataArgument(string $methodName, Node\Expr\MethodCall $methodCall): ?Node\Arg
    {
        if (count($methodCall->getArgs()) < 2) {
            return null;
        }

        // `make` just takes view name and data as arguments
        if ($methodName === self::MAKE) {
            return $methodCall->getArgs()[1];
        }

        // Here it can just be `renderWhen` or `renderUnless`
        if (count($methodCall->getArgs()) < 3) {
            return null;
        }

        // Second argument is the template data
        return $methodCall->getArgs()[2];
    }
}

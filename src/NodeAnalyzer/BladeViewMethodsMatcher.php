<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\NodeAnalyzer;

use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\View\Factory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use TomasVotruba\Bladestan\TemplateCompiler\ValueObject\RenderTemplateWithParameters;

final class BladeViewMethodsMatcher
{
    /**
     * @var string
     */
    private const MAKE = 'make';

    /**
     * @var string[]
     */
    private const VIEW_FACTORY_METHOD_NAMES = ['make', 'renderWhen', 'renderUnless'];

    public function __construct(
        private readonly TemplateFilePathResolver $templateFilePathResolver,
        private readonly ViewDataParametersAnalyzer $viewDataParametersAnalyzer
    ) {
    }

    /**
     * @return RenderTemplateWithParameters[]
     */
    public function match(MethodCall $methodCall, Scope $scope): array
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

        $templateNameArg = $this->findTemplateNameArg($methodName, $methodCall);

        if (! $templateNameArg instanceof Arg) {
            return [];
        }

        $template = $templateNameArg->value;

        $resolvedTemplateFilePaths = $this->templateFilePathResolver->resolveExistingFilePaths($template, $scope);

        if ($resolvedTemplateFilePaths === []) {
            return [];
        }

        $arg = $this->findTemplateDataArgument($methodName, $methodCall);

        if (! $arg instanceof Arg) {
            $parametersArray = new Array_();
        } else {
            $parametersArray = $this->viewDataParametersAnalyzer->resolveParametersArray($arg, $scope);
        }

        $result = [];

        foreach ($resolvedTemplateFilePaths as $resolvedTemplateFilePath) {
            $result[] = new RenderTemplateWithParameters($resolvedTemplateFilePath, $parametersArray);
        }

        return $result;
    }

    private function resolveName(MethodCall $methodCall): ?string
    {
        if (! $methodCall->name instanceof Identifier) {
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

    private function findTemplateNameArg(string $methodName, MethodCall $methodCall): ?Arg
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

    private function findTemplateDataArgument(string $methodName, MethodCall $methodCall): ?Arg
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

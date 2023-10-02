<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Registry;
use PHPStan\Rules\RuleError;
use TomasVotruba\Bladestan\Compiler\BladeToPHPCompiler;
use TomasVotruba\Bladestan\ErrorReporting\Blade\TemplateErrorsFactory;
use TomasVotruba\Bladestan\TemplateCompiler\ErrorFilter;
use TomasVotruba\Bladestan\TemplateCompiler\PHPStan\FileAnalyserProvider;
use TomasVotruba\Bladestan\TemplateCompiler\TypeAnalyzer\TemplateVariableTypesResolver;
use TomasVotruba\Bladestan\TemplateCompiler\ValueObject\RenderTemplateWithParameters;
use TomasVotruba\Bladestan\TemplateCompiler\ValueObject\VariableAndType;

final class ViewRuleHelper
{
    private Registry $registry;

    public function __construct(
        private readonly TemplateVariableTypesResolver $templateVariableTypesResolver,
        private readonly FileAnalyserProvider $fileAnalyserProvider,
        private readonly TemplateErrorsFactory $templateErrorsFactory,
        private readonly BladeToPHPCompiler $bladeToPhpCompiler,
        private readonly ErrorFilter $errorFilter,
    ) {
    }

    /**
     * @param RenderTemplateWithParameters[] $renderTemplatesWithParameters
     *
     * @return RuleError[]
     */
    public function processNode(
        FuncCall|MethodCall $call,
        Scope $scope,
        array $renderTemplatesWithParameters
    ): array {
        $ruleErrors = [];
        foreach ($renderTemplatesWithParameters as $renderTemplateWithParameter) {
            $variablesAndTypes = $this->templateVariableTypesResolver->resolveArray(
                $renderTemplateWithParameter->getParametersArray(),
                $scope
            );

            $currentRuleErrors = $this->processTemplateFilePath(
                $renderTemplateWithParameter->getTemplateFilePath(),
                $variablesAndTypes,
                $scope->getFile(),
                $call->getLine()
            );

            $ruleErrors = array_merge($ruleErrors, $currentRuleErrors);
        }

        return $ruleErrors;
    }

    /**
     * @param VariableAndType[] $variablesAndTypes
     *
     * @return RuleError[]
     */
    private function processTemplateFilePath(
        string $templateFilePath,
        array $variablesAndTypes,
        string $filePath,
        int $phpLine
    ): array {
        $fileContents = file_get_contents($templateFilePath);
        if ($fileContents === false) {
            return [];
        }

        $phpFileContentsWithLineMap = $this->bladeToPhpCompiler->compileContent(
            $templateFilePath,
            $fileContents,
            $variablesAndTypes
        );

        $phpFileContents = $phpFileContentsWithLineMap->getPhpFileContents();

        $tmpFilePath = sys_get_temp_dir() . '/' . md5($filePath) . '-blade-compiled.php';
        file_put_contents($tmpFilePath, $phpFileContents);

        $fileAnalyser = $this->fileAnalyserProvider->provide();

        $collectorsRegistry = new \PHPStan\Collectors\Registry([]);

        $fileAnalyserResult = $fileAnalyser->analyseFile(
            $tmpFilePath,
            [],
            $this->registry,
            $collectorsRegistry,
            null
        );

        /** @var Error[] $ruleErrors */
        $ruleErrors = $fileAnalyserResult->getErrors();

        $usefulRuleErrors = $this->errorFilter->filterErrors($ruleErrors);

        return $this->templateErrorsFactory->createErrors(
            $usefulRuleErrors,
            $phpLine,
            $filePath,
            $phpFileContentsWithLineMap,
        );
    }

    public function setRegistry(Registry $registry): void
    {
        $this->registry = $registry;
    }
}

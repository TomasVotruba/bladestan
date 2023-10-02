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
use TomasVotruba\Bladestan\ValueObject\CompiledTemplate;

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

            $compiledTemplate = $this->compileToPhp(
                $renderTemplateWithParameter->getTemplateFilePath(),
                $variablesAndTypes,
                $scope->getFile(),
            );

            if ($compiledTemplate === null) {
                continue;
            }

            $currentRuleErrors = $this->processTemplateFilePath(
                $compiledTemplate,
                $scope->getFile(),
                $call->getLine()
            );

            $ruleErrors = array_merge($ruleErrors, $currentRuleErrors);
        }

        return $ruleErrors;
    }

    /**
     * @return RuleError[]
     */
    private function processTemplateFilePath(
        CompiledTemplate $compiledTemplate,
        string           $bladeFilePath,
        int              $phpLine
    ): array {

        $fileAnalyser = $this->fileAnalyserProvider->provide();

        $collectorsRegistry = new \PHPStan\Collectors\Registry([]);

        $fileAnalyserResult = $fileAnalyser->analyseFile(
            $compiledTemplate->getFilePath(),
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
            $bladeFilePath,
            $compiledTemplate->getLineMap(),
        );
    }

    /**
     * @param VariableAndType[] $variablesAndTypes
     */
    private function compileToPhp(
        string $templateFilePath,
        array $variablesAndTypes,
        string $filePath,
    ): ?CompiledTemplate {
        $fileContents = file_get_contents($templateFilePath);
        if ($fileContents === false) {
            return null;
        }

        $phpFileContentsWithLineMap = $this->bladeToPhpCompiler->compileContent(
            $templateFilePath,
            $fileContents,
            $variablesAndTypes
        );

        $phpFileContents = $phpFileContentsWithLineMap->getPhpFileContents();

        $tmpFilePath = sys_get_temp_dir() . '/' . md5($filePath) . '-blade-compiled.php';
        file_put_contents($tmpFilePath, $phpFileContents);

        return new CompiledTemplate($tmpFilePath, $phpFileContentsWithLineMap);
    }

    public function setRegistry(Registry $registry): void
    {
        $this->registry = $registry;
    }
}

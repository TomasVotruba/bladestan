<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Registry;
use PHPStan\Rules\RuleError;
use PHPStan\ShouldNotHappenException;
use Symplify\TemplatePHPStanCompiler\PHPStan\FileAnalyserProvider;
use Symplify\TemplatePHPStanCompiler\TypeAnalyzer\TemplateVariableTypesResolver;
use Symplify\TemplatePHPStanCompiler\ValueObject\RenderTemplateWithParameters;
use Symplify\TemplatePHPStanCompiler\ValueObject\VariableAndType;
use Vural\PHPStanBladeRule\Compiler\BladeToPHPCompiler;
use Vural\PHPStanBladeRule\ErrorReporting\Blade\TemplateErrorsFactory;

use function array_merge;
use function file_get_contents;
use function file_put_contents;
use function md5;
use function sys_get_temp_dir;

final class ViewRuleHelper
{
    private Registry $registry;

    public function __construct(
        private TemplateVariableTypesResolver $templateVariableTypesResolver,
        private FileAnalyserProvider $fileAnalyserProvider,
        private TemplateErrorsFactory $templateErrorsFactory,
        private BladeToPHPCompiler $bladeToPhpCompiler,
    ) {
    }

    /**
     * @param RenderTemplateWithParameters[] $renderTemplatesWithParameters
     *
     * @return RuleError[]
     *
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope, array $renderTemplatesWithParameters): array
    {
        $ruleErrors = [];
        foreach ($renderTemplatesWithParameters as $renderTemplateWithParameter) {
            $variablesAndTypes = $this->templateVariableTypesResolver->resolveArray(
                $renderTemplateWithParameter->getParametersArray(),
                $scope
            );

            $currentRuleErrors = $this->processTemplateFilePath(
                $renderTemplateWithParameter->getTemplateFilePath(),
                $variablesAndTypes,
                $scope,
                $node->getLine()
            );

            $ruleErrors = array_merge($ruleErrors, $currentRuleErrors);
        }

        return $ruleErrors;
    }

    /**
     * @param VariableAndType[] $variablesAndTypes
     *
     * @return RuleError[]
     *
     * @throws ShouldNotHappenException
     */
    private function processTemplateFilePath(
        string $templateFilePath,
        array $variablesAndTypes,
        Scope $scope,
        int $phpLine
    ): array {
        $fileContents = file_get_contents($templateFilePath);

        if ($fileContents === false) {
            return [];
        }

        $phpFileContentsWithLineMap = $this->bladeToPhpCompiler->compileContent($templateFilePath, $fileContents, $variablesAndTypes);

        $phpFileContents = $phpFileContentsWithLineMap->getPhpFileContents();

        $tmpFilePath = sys_get_temp_dir() . '/' . md5($scope->getFile()) . '-blade-compiled.php';
        file_put_contents($tmpFilePath, $phpFileContents);

        $fileAnalyser = $this->fileAnalyserProvider->provide();

        $fileAnalyserResult = $fileAnalyser->analyseFile($tmpFilePath, [], $this->registry, null);

        $ruleErrors = $fileAnalyserResult->getErrors();

        return $this->templateErrorsFactory->createErrors(
            $ruleErrors,
            $phpLine,
            $scope->getFile(),
            $phpFileContentsWithLineMap,
        );
    }

    public function setRegistry(Registry $registry): void
    {
        $this->registry = $registry;
    }
}

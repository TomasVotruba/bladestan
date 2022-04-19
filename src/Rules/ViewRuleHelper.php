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
use function preg_match;
use function sys_get_temp_dir;

final class ViewRuleHelper
{
    private Registry $registry;

    private const ERRORS_TO_IGNORE = [
        'Call to function unset\(\) contains undefined variable \$loop\.',
        'Variable \$loop in PHPDoc tag @var does not exist\.',
        'Anonymous function has an unused use \$[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*\.',
    ];

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

        foreach ($ruleErrors as $key => $ruleError) {
            foreach (self::ERRORS_TO_IGNORE as $item) {
                if (! preg_match('#' . $item . '#', $ruleError->getMessage())) {
                    continue;
                }

                unset($ruleErrors[$key]);
            }
        }

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

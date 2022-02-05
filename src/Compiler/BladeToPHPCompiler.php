<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Compiler;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Events\Dispatcher;
use Illuminate\Events\NullDispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\FileViewFinder;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\ShouldNotHappenException;
use Symplify\TemplatePHPStanCompiler\NodeFactory\VarDocNodeFactory;
use Symplify\TemplatePHPStanCompiler\ValueObject\VariableAndType;
use Throwable;
use Vural\PHPStanBladeRule\Blade\PhpLineToTemplateLineResolver;
use Vural\PHPStanBladeRule\PHPParser\NodeVisitor\AddLoopVarTypeToForeachNodeVisitor;
use Vural\PHPStanBladeRule\PHPParser\NodeVisitor\RemoveEnvVariableNodeVisitor;
use Vural\PHPStanBladeRule\PHPParser\NodeVisitor\RemoveEscapeFunctionNodeVisitor;
use Vural\PHPStanBladeRule\ValueObject\PhpFileContentsWithLineMap;

use function array_merge;
use function getcwd;
use function preg_match_all;
use function preg_quote;
use function preg_replace;
use function sprintf;

final class BladeToPHPCompiler
{
    /**
     * @see https://regex101.com/r/BGw7Lf/1
     */
    private const VIEW_INCLUDE_REGEX = '#\$__env->make\(\'(.*?)\',( \[(.*?)?],)? \\\Illuminate\\\Support\\\Arr::except\(get_defined_vars\(\), \[\'__data\', \'__path\']\)\)->render\(\)#s';

    /**
     * @see https://regex101.com/r/BGw7Lf/1
     */
    private const VIEW_INCLUDE_REPLACE_REGEX = '#echo \$__env->make\(\'%s\',( \[(.*?)?],)? \\\Illuminate\\\Support\\\Arr::except\(get_defined_vars\(\), \[\'__data\', \'__path\']\)\)->render\(\);#s';

    private Parser $parser;

    /**
     * @param string[] $components
     * @phpstan-param array<int, array{class: string, alias: string, prefix: string}> $components
     */
    public function __construct(
        private Filesystem $fileSystem,
        private BladeCompiler $compiler,
        private Standard $printerStandard,
        private VarDocNodeFactory $varDocNodeFactory,
        private FileViewFinder $fileViewFinder,
        private FileNameAndLineNumberAddingPreCompiler $preCompiler,
        private PhpLineToTemplateLineResolver $phpLineToTemplateLineResolver,
        private PhpContentExtractor $phpContentExtractor,
        private array $components = [],
    ) {
        $parserFactory = new ParserFactory();
        $this->parser  = $parserFactory->create(ParserFactory::PREFER_PHP7);

        // Disable component rendering
        $this->compiler->withoutComponentTags();

        //$this->setupBladeComponents();
    }

    /**
     * @param array<VariableAndType> $variablesAndTypes
     *
     * @throws ShouldNotHappenException|FileNotFoundException
     */
    public function compileContent(string $filePath, array $variablesAndTypes): PhpFileContentsWithLineMap
    {
        $fileContent = $this->fileSystem->get($filePath);

        // TODO: extract class
        $fileContent = $this->preCompiler->setFileName($filePath)->compileString($fileContent);

        $rawPhpContent = $this->phpContentExtractor->extract($this->compiler->compileString($fileContent));

        $includes = $this->getIncludes($rawPhpContent);

        while ($includes !== []) {
            foreach ($includes as $include) {
                try {
                    $includedFilePath = $this->fileViewFinder->find($include);
                    $fileContents     = $this->fileSystem->get($includedFilePath);

                    $preCompiledContents = $this->preCompiler->setFileName($includedFilePath)->compileString($fileContents);
                    $compiledContent     = $this->compiler->compileString($preCompiledContents);
                    $includedContent     = $this->phpContentExtractor->extract(
                        $compiledContent,
                        false
                    );
                } catch (Throwable $e) {
                    $includedContent = '';
                }

                $rawPhpContent = preg_replace(sprintf(self::VIEW_INCLUDE_REPLACE_REGEX, preg_quote($include)), $includedContent, $rawPhpContent) ?? $rawPhpContent;
            }

            $includes = $this->getIncludes($rawPhpContent);
        }

        $decoratedPhpContent     = $this->decoratePhpContent($rawPhpContent, $variablesAndTypes);
        $phpLinesToTemplateLines = $this->phpLineToTemplateLineResolver->resolve($decoratedPhpContent);

        return new PhpFileContentsWithLineMap($decoratedPhpContent, $phpLinesToTemplateLines);
    }

    /**
     * @param VariableAndType[] $variablesAndTypes
     */
    private function decoratePhpContent(string $phpContent, array $variablesAndTypes): string
    {
        $stmts = $this->parser->parse($phpContent);
        if ($stmts === null) {
            // TODO create our own exception
            throw new ShouldNotHappenException();
        }

        // Apply some visitors
        // - get rid of $__env variables
        // - get rid of e() function calls
        $stmts = $this->traverseStmtsWithVisitors($stmts, [
            new RemoveEnvVariableNodeVisitor(),
            new RemoveEscapeFunctionNodeVisitor(),
            new AddLoopVarTypeToForeachNodeVisitor(),
        ]);

        // Add @var docs to top of file
        $stmts = array_merge($this->varDocNodeFactory->createDocNodes($variablesAndTypes), $stmts);

        return $this->printerStandard->prettyPrintFile($stmts);
    }

    /**
     * @param Stmt[]                $stmts
     * @param NodeVisitorAbstract[] $nodeVisitors
     *
     * @return Node[]
     */
    private function traverseStmtsWithVisitors(array $stmts, array $nodeVisitors): array
    {
        $nodeTraverser = new NodeTraverser();
        foreach ($nodeVisitors as $nodeVisitor) {
            $nodeTraverser->addVisitor($nodeVisitor);
        }

        return $nodeTraverser->traverse($stmts);
    }

    /** @return string[] */
    private function getIncludes(string $compiled): array
    {
        preg_match_all(self::VIEW_INCLUDE_REGEX, $compiled, $includes);

        return $includes[1];
    }

    private function setupBladeComponents(): void
    {
        $currentWorkingDirectory = getcwd();

        if ($currentWorkingDirectory === false) {
            return;
        }

        //Hack to make the compiler work
        $application = new Application($currentWorkingDirectory);
        $application->bind(\Illuminate\Contracts\Foundation\Application::class, static fn () => $application);
        $application->bind(Factory::class, fn () => new \Illuminate\View\Factory(new EngineResolver(), $this->fileViewFinder, new NullDispatcher(new Dispatcher())));

        $application->alias('view', 'foo');

        //Register components
        foreach ($this->components as $component) {
            $this->compiler->component($component['class'], $component['alias'], $component['prefix']);
        }
    }
}

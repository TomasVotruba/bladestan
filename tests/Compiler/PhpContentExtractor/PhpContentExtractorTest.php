<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Compiler\PhpContentExtractor;

use Illuminate\View\Compilers\BladeCompiler;
use Iterator;
use PHPStan\Testing\PHPStanTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use TomasVotruba\Bladestan\Compiler\FileNameAndLineNumberAddingPreCompiler;
use TomasVotruba\Bladestan\Compiler\PhpContentExtractor;
use TomasVotruba\Bladestan\Tests\TestUtils;

final class PhpContentExtractorTest extends PHPStanTestCase
{
    private PhpContentExtractor $phpContentExtractor;

    private BladeCompiler $bladeCompiler;

    private FileNameAndLineNumberAddingPreCompiler $fileNameAndLineNumberAddingPreCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phpContentExtractor = self::getContainer()->getByType(PhpContentExtractor::class);
        $this->bladeCompiler = self::getContainer()->getByType(BladeCompiler::class);
        $this->fileNameAndLineNumberAddingPreCompiler = self::getContainer()->getByType(
            FileNameAndLineNumberAddingPreCompiler::class
        );
    }

    #[DataProvider('fixtureProvider')]
    public function testExtactPhpContentsFromBladeTemlate(string $filePath): void
    {
        [$inputBladeContents, $expectedPhpContents] = TestUtils::splitFixture($filePath);

        $fileContent = $this->fileNameAndLineNumberAddingPreCompiler
            ->setFileName('/some-directory-name/resources/views/foo.blade.php')
            ->compileString($inputBladeContents);

        $compiledPhpContents = $this->bladeCompiler->compileString($fileContent);
        $phpFileContent = $this->phpContentExtractor->extract($compiledPhpContents);

        $this->assertSame($expectedPhpContents, $phpFileContent);
    }

    public static function fixtureProvider(): Iterator
    {
        return TestUtils::yieldDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../config/extension.neon'];
    }
}

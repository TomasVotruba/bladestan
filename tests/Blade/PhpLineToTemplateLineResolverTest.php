<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Tests\Blade;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use TomasVotruba\Bladestan\Blade\PhpLineToTemplateLineResolver;
use TomasVotruba\Bladestan\Tests\AbstractTestCase;

final class PhpLineToTemplateLineResolverTest extends AbstractTestCase
{
    private PhpLineToTemplateLineResolver $phpLineToTemplateLineResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->phpLineToTemplateLineResolver = $this->getService(PhpLineToTemplateLineResolver::class);
    }

    /**
     * @param mixed[] $expectedPhpToTemplateLineMapping
     */
    #[DataProvider('provideData')]
    public function test(string $phpContent, array $expectedPhpToTemplateLineMapping): void
    {
        $phpToTemplateLineMapping = $this->phpLineToTemplateLineResolver->resolve($phpContent);

        $this->assertSame($expectedPhpToTemplateLineMapping, $phpToTemplateLineMapping);
    }

    public static function provideData(): Iterator
    {
        yield 'File with no contents' => [
            '',
            [],
        ];

        yield 'File with no comments' => [
            "<?php echo 'foo';",
            [],
        ];

        yield 'File with wrong comment style' => [
            <<<'PHP'
                <?php
                // file: foo.blade.php, line: 5 */
                echo 'foo';
                /* file: foo.blade.php, line: 6 */
                echo 'foo';
PHP,
            [],
        ];

        yield 'Simple file' => [
            <<<'PHP'
                <?php
                /** file: foo.blade.php, line: 5 */
                echo 'foo';
PHP,
            [
                3 => [
                    'foo.blade.php' => 5,
                ],
            ],
        ];

        yield 'File with multiple lines' => [
            <<<'PHP'
                <?php
                /** file: foo.blade.php, line: 5 */
                echo 'foo';
                /** file: foo.blade.php, line: 55 */
                echo 'bar';
PHP,
            [
                3 => [
                    'foo.blade.php' => 5,
                ],
                5 => [
                    'foo.blade.php' => 55,
                ],
            ],
        ];

        yield 'File with multiple file names' => [
            <<<'PHP'
                <?php
                /** file: foo.blade.php, line: 5 */
                echo 'foo';
                /** file: foo.blade.php, line: 6 */
                echo 'bar';
                /** file: bar.blade.php, line: 55 */
                echo 'baz';
PHP,
            [
                3 => [
                    'foo.blade.php' => 5,
                ],
                5 => [
                    'foo.blade.php' => 6,
                ],
                7 => [
                    'bar.blade.php' => 55,
                ],
            ],
        ];
    }
}

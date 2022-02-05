<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Compiler;

use function array_map;
use function array_unshift;
use function implode;
use function preg_match_all;
use function str_starts_with;
use function trim;

use const PHP_EOL;

final class PhpContentExtractor
{
    /**
     * @see https://regex101.com/r/ENwa4O/1
     */
    private const PHP_OPEN_CLOSE_TAGS_REGEX = '#^(/\*\* file: .*?, line: \d+ \*/)(?!\s?/\*\* file: ).*?<\?php(.*?)\?>$#ms';

    /**
     * @param string $bladeCompiledContent This should be the string that is pre-compiled for Blade and then compiled by Blade.
     */
    public function extract(string $bladeCompiledContent, bool $addPHPOpeningTag = true): string
    {
        preg_match_all(self::PHP_OPEN_CLOSE_TAGS_REGEX, $bladeCompiledContent, $matches);

        foreach ($matches[1] as $key => $match) {
            if ($match !== '' || str_starts_with(trim($matches[2][$key]), 'echo $__env->make')) {
                continue;
            }

            $matches[1][$key] = $matches[1][$key - 1];
        }

        $phpContents = array_map(static fn ($a, $b) => $a . $b, $matches[1], $matches[2]);

        if ($phpContents !== [] && $addPHPOpeningTag) {
            array_unshift($phpContents, '<?php');
        }

        return implode(PHP_EOL, $phpContents);
    }
}

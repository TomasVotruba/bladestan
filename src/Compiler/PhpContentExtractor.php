<?php

declare(strict_types=1);

namespace Vural\PHPStanBladeRule\Compiler;

use function array_map;
use function array_unshift;
use function explode;
use function implode;
use function preg_match;
use function preg_match_all;
use function rtrim;
use function str_replace;
use function str_starts_with;
use function strip_tags;
use function token_get_all;
use function token_name;
use function trim;

use const PHP_EOL;

final class PhpContentExtractor
{
    /**
     * @see https://regex101.com/r/WomL6O/1
     */
    private const PHP_OPEN_CLOSE_TAGS_REGEX = '#^(/\*\* file: .*?, line: \d+ \*/)(?!\s?/\*\* file: ).*?<\?php(.*?)\?>$#ms';

    private const TEMPLATE_FILE_NAME_AND_LINE_NUMBER_STRICT_REGEX = '#^(/\*\* file: .*?, line: \d+ \*/)$#m';

    /**
     * @param string $bladeCompiledContent This should be the string that is pre-compiled for Blade and then compiled by Blade.
     */
    public function extract(string $bladeCompiledContent, bool $addPHPOpeningTag = true): string
    {
        $bladeCompiledContent = $this->removeHtmlTags($bladeCompiledContent);
        $bladeCompiledContent = $this->removeEmptyLines($bladeCompiledContent);

        preg_match_all(self::PHP_OPEN_CLOSE_TAGS_REGEX, $bladeCompiledContent, $matches);

        foreach ($matches[1] as $key => $match) {
            if ($match !== '' || str_starts_with(trim($matches[2][$key]), 'echo $__env->make')) {
                continue;
            }

            $matches[1][$key] = $matches[1][$key - 1];
        }

        $phpContents = array_map(static fn ($a, $b) => $a . rtrim($b), $matches[1], $matches[2]);

        if ($phpContents !== [] && $addPHPOpeningTag) {
            array_unshift($phpContents, '<?php');
        }

        return implode(PHP_EOL, $phpContents);
    }

    private function removeHtmlTags(string $input): string
    {
        $strippedInput = $input;
        $tokens        = token_get_all($input);

        foreach ($tokens as $token) {
            if (token_name((int) $token[0]) !== 'T_INLINE_HTML') {
                continue;
            }

            $strippedInput = str_replace($token[1], strip_tags($token[1]), $strippedInput);
        }

        return str_replace('">', '', $strippedInput);
    }

    private function removeEmptyLines(string $bladeCompiledContent): string
    {
        $lines = explode(PHP_EOL, $bladeCompiledContent);

        foreach ($lines as $key => $line) {
            $trimmedLine = trim($line);

            if (! preg_match(self::TEMPLATE_FILE_NAME_AND_LINE_NUMBER_STRICT_REGEX, $trimmedLine)) {
                continue;
            }

            unset($lines[$key]);
        }

        return implode(PHP_EOL, $lines);
    }
}

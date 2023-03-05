<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\Compiler;

final class PhpContentExtractor
{
    /**
     * @see https://regex101.com/r/WomL6O/1
     * @var string
     */
    private const PHP_OPEN_CLOSE_TAGS_REGEX = '#^(/\*\* file: .*?, line: \d+ \*/)(?!\s?/\*\* file: ).*?<\?php(.*?)\?>$#ms';

    /**
     * @var string
     */
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
            if ($match !== '') {
                continue;
            }

            if (str_starts_with(trim((string) $matches[2][$key]), 'echo $__env->make')) {
                continue;
            }

            $matches[1][$key] = $matches[1][$key - 1];
        }

        $phpContents = array_map(static fn ($a, $b): string => $a . rtrim((string) $b), $matches[1], $matches[2]);

        if ($phpContents !== [] && $addPHPOpeningTag) {
            array_unshift($phpContents, '<?php');
        }

        return implode(PHP_EOL, $phpContents);
    }

    private function removeHtmlTags(string $input): string
    {
        $strippedInput = $input;
        $tokens = token_get_all($input);

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

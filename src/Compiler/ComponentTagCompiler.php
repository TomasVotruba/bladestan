<?php

namespace TomasVotruba\Bladestan\Compiler;

use Illuminate\Support\Str;
use Illuminate\View\AnonymousComponent;
use Illuminate\View\Compilers\BladeCompiler;

class ComponentTagCompiler
{
    /**
     * The Blade compiler instance.
     */
    protected \Illuminate\View\Compilers\BladeCompiler $blade;

    /**
     * The "bind:" attributes that have been compiled for the current component.
     *
     * @var array<string, bool>
     */
    protected array $boundAttributes = [];

    /**
     * Create a new component tag compiler.
     */
    public function __construct(BladeCompiler $blade)
    {
        $this->blade = $blade;
    }

    /**
     * Compile the component and slot tags within the given string.
     */
    public function compile(string $value): string
    {
        $value = $this->compileSlots($value);

        return $this->compileTags($value);
    }

    /**
     * Compile the tags within the given string.
     */
    public function compileTags(string $value): string
    {
        $value = $this->compileSelfClosingTags($value);
        $value = $this->compileOpeningTags($value);
        $value = $this->compileClosingTags($value);

        return $value;
    }

    /**
     * Compile the slot tags within the given string.
     */
    public function compileSlots(string $value): string
    {
        $pattern = "/
            <
                \s*
                x[\-\:]slot
                (?:\:(?<inlineName>\w+(?:-\w+)*))?
                (?:\s+(:?)name=(?<name>(\"[^\"]+\"|\\\'[^\\\']+\\\'|[^\s>]+)))?
                (?<attributes>
                    (?:
                        \s+
                        (?:
                            (?:
                                @(?:class)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                @(?:style)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                            )
                            |
                            (?:
                                [\w\-:.@]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \'[^\']*\'
                                        |
                                        [^\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \s*
                )
                (?<![\/=\-])
            >
        /x";

        $value = preg_replace_callback($pattern, function ($matches) {
            $name = $this->stripQuotes($matches['inlineName'] ?: $matches['name']);

            if (Str::contains($name, '-') && ! empty($matches['inlineName'])) {
                $name = Str::camel($name);
            }

            if ($matches[2] !== ':') {
                $name = "'{$name}'";
            }

            $this->boundAttributes = [];

            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            return " @slot({$name}, null, [" . $this->attributesToString($attributes) . ']) ';
        }, $value) ?? throw new \Exception('preg_replace_callback error');

        return preg_replace('/<\/\s*x[\-\:]slot[^>]*>/', ' @endslot', $value) ?? $value;
    }

    /**
     * Strip any quotes from the given string.
     */
    public function stripQuotes(string $value): string
    {
        return Str::startsWith($value, ['"', '\''])
                    ? substr($value, 1, -1)
                    : $value;
    }

    /**
     * Compile the opening tags within the given string.
     */
    protected function compileOpeningTags(string $value): string
    {
        $pattern = "/
            <
                \s*
                x[-\:]([\w\-\:\.]*)
                (?<attributes>
                    (?:
                        \s+
                        (?:
                            (?:
                                @(?:class)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                @(?:style)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                            )
                            |
                            (?:
                                (\:\\\$)(\w+)
                            )
                            |
                            (?:
                                [\w\-:.@]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \'[^\']*\'
                                        |
                                        [^\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \s*
                )
                (?<![\/=\-])
            >
        /x";

        return preg_replace_callback($pattern, function (array $matches) {
            $this->boundAttributes = [];

            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            return $this->componentString($matches[1], $attributes);
        }, $value) ?? throw new \Exception('preg_replace_callback error');
    }

    /**
     * Compile the self-closing tags within the given string.
     */
    protected function compileSelfClosingTags(string $value): string
    {
        $pattern = "/
            <
                \s*
                x[-\:]([\w\-\:\.]*)
                \s*
                (?<attributes>
                    (?:
                        \s+
                        (?:
                            (?:
                                @(?:class)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                @(?:style)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                            )
                            |
                            (?:
                                (\:\\\$)(\w+)
                            )
                            |
                            (?:
                                [\w\-:.@]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \'[^\']*\'
                                        |
                                        [^\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \s*
                )
            \/>
        /x";

        return preg_replace_callback($pattern, function (array $matches) {
            $this->boundAttributes = [];

            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            return $this->componentString($matches[1], $attributes) . "\n";
        }, $value) ?? throw new \Exception('preg_replace_callback error');
    }

    /**
     * Compile the Blade component string for the given component and attributes.
     * @param array<string, mixed> $attributes
     */
    protected function componentString(string $component, array $attributes): string
    {
        $parameters = [
            'view' => "'{$component}'",
            'data' => '[' . $this->attributesToString($attributes, $escapeBound = false) . ']',
        ];

        $class = AnonymousComponent::class;
        $attrString = $this->attributesToString($parameters, $escapeBound = false);

        return "<?php {$class}::resolve([{$attrString}]); ?>";
    }

    /**
     * Compile the closing tags within the given string.
     */
    protected function compileClosingTags(string $value): string
    {
        return preg_replace("/<\/\s*x[-\:][\w\-\:\.]*\s*>/", '', $value) ?? $value;
    }

    /**
     * Get an array of attributes from the given attribute string.
     *
     * @return array<array-key, mixed>
     */
    protected function getAttributesFromAttributeString(string $attributeString): array
    {
        $attributeString = $this->parseShortAttributeSyntax($attributeString);
        $attributeString = $this->parseAttributeBag($attributeString);
        $attributeString = $this->parseComponentTagClassStatements($attributeString);
        $attributeString = $this->parseComponentTagStyleStatements($attributeString);
        $attributeString = $this->parseBindAttributes($attributeString);

        $pattern = '/
            (?<attribute>[\w\-:.@]+)
            (
                =
                (?<value>
                    (
                        \"[^\"]+\"
                        |
                        \\\'[^\\\']+\\\'
                        |
                        [^\s>]+
                    )
                )
            )?
        /x';

        if (! preg_match_all($pattern, $attributeString, $matches, PREG_SET_ORDER)) {
            return [];
        }

        /** @var list<array<array-key, string>> $matches */
        return collect($matches)->mapWithKeys(function ($match) {
            $attribute = $match['attribute'];
            $value = $match['value'] ?? null;

            if ($value === null) {
                $value = 'true';

                $attribute = Str::start($attribute, 'bind:');
            }

            $value = $this->stripQuotes($value);

            if (str_starts_with($attribute, 'bind:')) {
                $attribute = Str::after($attribute, 'bind:');

                $this->boundAttributes[$attribute] = true;
            } else {
                $value = "'" . $this->compileAttributeEchos($value) . "'";
            }

            if (str_starts_with($attribute, '::')) {
                $attribute = substr($attribute, 1);
            }

            return [
                $attribute => $value,
            ];
        })->toArray();
    }

    /**
     * Parses a short attribute syntax like :$foo into a fully-qualified syntax like :foo="$foo".
     */
    protected function parseShortAttributeSyntax(string $value): string
    {
        $pattern = "/\s\:\\\$(\w+)/x";

        return preg_replace_callback(
            $pattern,
            fn (array $matches) => " :{$matches[1]}=\"\${$matches[1]}\"",
            $value
        ) ?? throw new \Exception('preg_replace_callback error');
    }

    /**
     * Parse the attribute bag in a given attribute string into its fully-qualified syntax.
     */
    protected function parseAttributeBag(string $attributeString): string
    {
        $pattern = "/
            (?:^|\s+)                                        # start of the string or whitespace between attributes
            \{\{\s*(\\\$attributes(?:[^}]+?(?<!\s))?)\s*\}\} # exact match of attributes variable being echoed
        /x";

        return preg_replace($pattern, ' :attributes="$1"', $attributeString) ?? $attributeString;
    }

    /**
     * Parse @class statements in a given attribute string into their fully-qualified syntax.
     */
    protected function parseComponentTagClassStatements(string $attributeString): string
    {
        return preg_replace_callback(
            '/@(class)(\( ( (?>[^()]+) | (?2) )* \))/x',
            function ($match) {
                if ($match[1] === 'class') {
                    $match[2] = str_replace('"', "'", $match[2]);

                    return ":class=\"\Illuminate\Support\Arr::toCssClasses{$match[2]}\"";
                }

                return $match[0];
            },
            $attributeString
        ) ?? throw new \Exception('preg_replace_callback error');
    }

    /**
     * Parse @style statements in a given attribute string into their fully-qualified syntax.
     */
    protected function parseComponentTagStyleStatements(string $attributeString): string
    {
        return preg_replace_callback(
            '/@(style)(\( ( (?>[^()]+) | (?2) )* \))/x',
            function ($match) {
                if ($match[1] === 'style') {
                    $match[2] = str_replace('"', "'", $match[2]);

                    return ":style=\"\Illuminate\Support\Arr::toCssStyles{$match[2]}\"";
                }

                return $match[0];
            },
            $attributeString
        ) ?? throw new \Exception('preg_replace_callback error');
    }

    /**
     * Parse the "bind" attributes in a given attribute string into their fully-qualified syntax.
     */
    protected function parseBindAttributes(string $attributeString): string
    {
        $pattern = "/
            (?:^|\s+)     # start of the string or whitespace between attributes
            :(?!:)        # attribute needs to start with a single colon
            ([\w\-:.@]+)  # match the actual attribute name
            =             # only match attributes that have a value
        /xm";

        return preg_replace($pattern, ' bind:$1=', $attributeString) ?? $attributeString;
    }

    /**
     * Compile any Blade echo statements that are present in the attribute string.
     *
     * These echo statements need to be converted to string concatenation statements.
     */
    protected function compileAttributeEchos(string $attributeString): string
    {
        $value = $this->blade->compileEchos($attributeString);

        $value = $this->escapeSingleQuotesOutsideOfPhpBlocks($value);

        $value = str_replace('<?php echo ', '\'.', $value);
        $value = str_replace('; ?>', '.\'', $value);

        return $value;
    }

    /**
     * Escape the single quotes in the given string that are outside of PHP blocks.
     */
    protected function escapeSingleQuotesOutsideOfPhpBlocks(string $value): string
    {
        return collect(token_get_all($value))
            ->map(function ($token) {
                if (! is_array($token)) {
                    return $token;
                }

                return $token[0] === T_INLINE_HTML
                            ? str_replace("'", "\\'", $token[1])
                            : $token[1];
            })->implode('');
    }

    /**
     * Convert an array of attributes to a string.
     * @param array<string, mixed> $attributes
     */
    protected function attributesToString(array $attributes, bool $escapeBound = true): string
    {
        return collect($attributes)
            ->map(
                fn (string $value, string $attribute) => $escapeBound && isset($this->boundAttributes[$attribute]) && $value !== 'true' && ! is_numeric(
                    $value
                )
                        ? "'{$attribute}' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute({$value})"
                        : "'{$attribute}' => {$value}"
            )
            ->implode(',');
    }
}

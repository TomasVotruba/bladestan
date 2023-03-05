{{
    /** @var string $foo */
    $foo
}}

{{ $bar }}
-----
/** file: foo.blade.php, line: 1 */{{
    /** @var string $foo */
/** file: foo.blade.php, line: 3 */    $foo
/** file: foo.blade.php, line: 4 */}}

/** file: foo.blade.php, line: 6 */{{ $bar }}

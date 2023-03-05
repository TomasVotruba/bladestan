{{ $foo }}


{{
    $bar
}}
-----
/** file: foo.blade.php, line: 1 */{{ $foo }}


/** file: foo.blade.php, line: 4 */{{
/** file: foo.blade.php, line: 5 */    $bar
/** file: foo.blade.php, line: 6 */}}

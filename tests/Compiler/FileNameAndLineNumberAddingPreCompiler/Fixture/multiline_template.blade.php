<h1>
    {{ $foo }}
</h1>
-----
/** file: foo.blade.php, line: 1 */<h1>
/** file: foo.blade.php, line: 2 */    {{ $foo }}
/** file: foo.blade.php, line: 3 */</h1>

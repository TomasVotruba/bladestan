<?php

declare(strict_types=1);

namespace LaravelViewFunction;

use function view;

view('foo', [
    'foo' => 'bar',
]);

view('php_directive_with_comment', []);

view('dummyNamespace::namespacedView', [
    'variable' => 'foobar',
]);

view('simple_variable')
    ->withFoo('bar')
    ->withBar(10);
view('simple_variable')->with('foo', 'bar');

view('file_with_include', [
    'foo' => 'foo',
]);

view('file_with_recursive_include', [
    'foo' => 'foo',
]);

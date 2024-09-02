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

$foo = 'foo';
view('simple_variable', compact('foo'));

view('include_with_parameters', [
    'includeData' => [],
]);

view('static_content');

view('empty');

view('nested-foreach');

$fooBar = [
    'foo' => 'bar',
];

view('foo', $fooBar);

class MyDto implements \Illuminate\Contracts\Support\Arrayable {
    /**
     * @return array{foo: string}
     */
    public function toArray() {
        return ['foo' => 'bar'];
    }
}

view('foo', new MyDto());

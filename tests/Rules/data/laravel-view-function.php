<?php

declare(strict_types=1);

namespace LaravelViewFunction;

use function view;

view('foo', ['foo' => 'bar']);

view('php_directive_with_comment', []);

view('dummyNamespace::home', ['variable' => 'foobar']);

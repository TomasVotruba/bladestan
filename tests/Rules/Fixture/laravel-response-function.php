<?php

declare(strict_types=1);

namespace LaravelResponseFunction;

use function response;

response()->view('foo', [
    'foo' => 'bar',
]);

<?php

declare(strict_types=1);

namespace LaravelComponentFunction;

use Closure;
use Illuminate\View\Component;

class MyViewComponent extends Component
{
    /**
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('component', [
            'foo' => 'bar',
        ]);
    }
}

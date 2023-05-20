<?php

declare(strict_types=1);

namespace LaravelComponentMethod;

use Closure;
use Illuminate\View\Component;
use Luxplus\Core\Database\Model\Languages\Language;

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

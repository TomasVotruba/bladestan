<?php

declare(strict_types=1);

namespace LaravelComponentMethod;

use Closure;
use Illuminate\View\Component;
use Luxplus\Core\Database\Model\Languages\Language;

class MyComponent extends Component
{
    /**
     * @return Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return $this->view('component', [
            'foo' => 'bar',
        ]);
    }
}

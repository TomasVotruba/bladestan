<?php

declare(strict_types=1);

namespace LaravelMailableMethod;

use Illuminate\Mail\Mailable;

class MyMailable extends Mailable
{
    /**
     * @return $this
     */
    public function build()
    {
        return $this->view('foo', [
            'foo' => 'bar',
        ]);
    }
}

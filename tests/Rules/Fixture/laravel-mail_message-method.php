<?php

declare(strict_types=1);

namespace LaravelMailMessageMethod;

use Illuminate\Notifications\Messages\MailMessage;

class MyMailMessage extends MailMessage
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

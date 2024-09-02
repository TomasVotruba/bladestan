<?php

declare(strict_types=1);

namespace LaravelMailableMethod;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;

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

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'foo',
            with: [
                'foo' => 'bar',
            ],
        );
    }
}

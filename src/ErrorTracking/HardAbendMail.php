<?php

namespace UniqueWorkbench\SharedUi\ErrorTracking;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HardAbendMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ErrorLog $errorLog)
    {
    }

    public function envelope(): Envelope
    {
        $status = $this->errorLog->status_code ? "{$this->errorLog->status_code} " : '';

        return new Envelope(
            subject: "[{$this->errorLog->app}/{$this->errorLog->environment}] {$status}{$this->errorLog->exception_class}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'shared-ui::mail.hard-abend',
        );
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TontineNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $content;
    public $actionUrl;

    public function __construct($title, $content, $actionUrl = null)
    {
        $this->title = $title;
        $this->content = $content;
        $this->actionUrl = $actionUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[TontineChaine] " . $this->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
        );
    }
}

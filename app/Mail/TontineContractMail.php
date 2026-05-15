<?php

namespace App\Mail;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TontineContractMail extends Mailable
{
    use Queueable, SerializesModels;

    public $group;
    public $explorerUrl;
    protected $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Group $group, $pdfContent, $explorerUrl = null)
    {
        $this->group = $group;
        $this->pdfContent = $pdfContent;
        $this->explorerUrl = $explorerUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "📜 Contrat & Preuve Blockchain - " . $this->group->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contract',
            with: [
                'groupName' => $this->group->name,
                'explorerUrl' => $this->explorerUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, 'Contrat_Tontine_'.$this->group->name.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}

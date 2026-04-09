<?php

namespace App\Mail;

use App\Models\FicheObjectif;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FicheObjectifAssigneeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly FicheObjectif $fiche,
        public readonly string $recipientName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle fiche d\'objectifs assignée',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.fiche-objectif-assignee',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

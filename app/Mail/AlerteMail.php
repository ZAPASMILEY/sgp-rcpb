<?php

namespace App\Mail;

use App\Models\Alerte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlerteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Alerte $alerte,
        public readonly string $recipientName,
    ) {}

    public function envelope(): Envelope
    {
        $prefix = match ($this->alerte->priorite) {
            'critique' => '🔴 CRITIQUE',
            'haute'    => '🟠 HAUTE',
            'moyenne'  => '🟡',
            default    => '',
        };

        return new Envelope(
            subject: trim($prefix . ' — Alerte SGP-RCPB : ' . $this->alerte->titre),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alerte',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

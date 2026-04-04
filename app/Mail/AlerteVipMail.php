<?php

namespace App\Mail;

use App\Models\Alerte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlerteVipMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Alerte $alerte,
        public readonly string $recipientName,
        public readonly string $recipientRole,
    ) {}

    public function envelope(): Envelope
    {
        $prefix = match ($this->alerte->priorite) {
            'critique' => '🔴 URGENT',
            'haute'    => '🟠 IMPORTANT',
            'moyenne'  => '🟡',
            default    => '',
        };

        return new Envelope(
            subject: trim($prefix . ' — Alerte Direction SGP-RCPB : ' . $this->alerte->titre),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alerte-vip',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

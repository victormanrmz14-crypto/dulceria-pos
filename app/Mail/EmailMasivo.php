<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailMasivo extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $usuario,
        public readonly string $asunto,
        public readonly string $mensaje,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->asunto);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.email-masivo');
    }

    public function attachments(): array { return []; }
}

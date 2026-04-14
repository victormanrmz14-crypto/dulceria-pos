<?php

namespace App\Mail;

use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificarProveedor extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Proveedor $proveedor,
        public readonly Producto  $producto,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Stock bajo: {$this->producto->nombre}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notificar-proveedor',
        );
    }
}

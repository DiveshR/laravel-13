<?php

namespace App\Mail;

use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExportReady extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Export $export) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: ucfirst($this->export->type) . ' Export Ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.export-ready',
        );
    }
}

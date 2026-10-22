<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Shob email (registration, chamber, approval, login...) ek-i template (emails.notice) use kore.
 *
 * $payload keys: tone (success|warning|danger|info), badge, title, greeting,
 *                lines[], details[label => value], button[text,url], footnote
 */
class NoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $subjectLine, public array $payload = [])
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.notice', with: $this->payload);
    }
}

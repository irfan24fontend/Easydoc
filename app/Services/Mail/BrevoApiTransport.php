<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

/**
 * Brevo (ex-Sendinblue) er HTTP API diye mail pathay.
 * SMTP port (25/465/587) lage na, tai hosting provider SMTP block korle o kaj kore.
 * API docs: https://developers.brevo.com/reference/sendtransacemail
 */
class BrevoApiTransport extends AbstractTransport
{
    public function __construct(private string $apiKey)
    {
        parent::__construct();
    }

    public function __toString(): string
    {
        return 'brevo+api';
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $from = $email->getFrom()[0] ?? null;

        $payload = [
            'sender'      => $from ? $this->addr($from) : null,
            'to'          => array_map($this->addr(...), $email->getTo()),
            'subject'     => (string) $email->getSubject(),
            'htmlContent' => $email->getHtmlBody() ?: null,
            'textContent' => $email->getTextBody() ?: null,
        ];

        if ($cc = $email->getCc()) {
            $payload['cc'] = array_map($this->addr(...), $cc);
        }
        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = array_map($this->addr(...), $bcc);
        }
        if ($replyTo = $email->getReplyTo()) {
            $payload['replyTo'] = $this->addr($replyTo[0]);
        }

        $payload = array_filter($payload, fn ($v) => $v !== null && $v !== []);

        $response = Http::withHeaders([
            'api-key'      => $this->apiKey,
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', $payload);

        if ($response->failed()) {
            throw new TransportException(
                'Brevo API email pathano gelo na: ' . $response->status() . ' ' . $response->body()
            );
        }
    }

    private function addr(Address $address): array
    {
        return array_filter([
            'email' => $address->getAddress(),
            'name'  => $address->getName() ?: null,
        ]);
    }
}

<?php

namespace App\Services\Ai;

use Exception;
use Throwable;

/**
 * AI provider (Gemini/Grok) kono karone response dite na parle throw hoy.
 */
class AiUnavailableException extends Exception
{
    public readonly array $attempts;

    public function __construct(
        array $attempts = [],
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        $this->attempts = $attempts;
        $count = count($attempts);

        parent::__construct(
            $message !== '' ? $message : "AI service {$count} bar try korar porew response deyni.",
            $code,
            $previous
        );
    }
}
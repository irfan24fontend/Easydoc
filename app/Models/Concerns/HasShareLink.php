<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Prescription model e add korun:  use \App\Models\Concerns\HasShareLink;
 */
trait HasShareLink
{
    public function ensureShareToken(): string
    {
        if (! $this->share_token) {
            do {
                $token = Str::random(40);
            } while (static::where('share_token', $token)->exists());

            $this->forceFill(['share_token' => $token, 'shared_at' => now()])->save();
        }

        return $this->share_token;
    }

    public function revokeShareToken(): void
    {
        $this->forceFill(['share_token' => null, 'shared_at' => null])->save();
    }

    public function shareUrl(): string
    {
        return route('prescription.public', $this->ensureShareToken());
    }
}

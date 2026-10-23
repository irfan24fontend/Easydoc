<?php

namespace App\Providers;

use App\Services\Mail\BrevoApiTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class BrevoMailServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Mail::extend('brevo-api', function (array $config = []) {
            return new BrevoApiTransport($config['key'] ?? config('services.brevo.key'));
        });
    }
}

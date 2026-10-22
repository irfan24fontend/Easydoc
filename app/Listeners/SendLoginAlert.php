<?php

namespace App\Listeners;

use App\Services\MailNotifier;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cache;

/**
 * Method er naam onLogin rakha hoyeche (handle na) jeno Laravel er auto-discovery
 * duibar register na kore. MailEventServiceProvider e manually register kora.
 */
class SendLoginAlert
{
    public function onLogin(Login $event): void
    {
        if (! config('mailnotify.login_alert') || app()->runningInConsole()) {
            return;
        }

        $user = $event->user;
        $ip   = request()->ip();
        $ua   = request()->userAgent();

        // Same user + IP + browser theke koyek ghontar moddhe abar email jabe na
        $key = 'login-alert:' . $user->getAuthIdentifier() . ':' . sha1($ip . '|' . $ua);

        if (! Cache::add($key, 1, now()->addHours(max(1, (int) config('mailnotify.login_alert_hours', 6))))) {
            return;
        }

        app(MailNotifier::class)->loginAlert($user, $ip, $ua);
    }
}

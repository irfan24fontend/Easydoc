<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin notification emails
    |--------------------------------------------------------------------------
    | Comma separated list in .env:  ADMIN_NOTIFY_EMAILS=admin@gmail.com,other@gmail.com
    | Khali thakle MAIL_FROM_ADDRESS e jabe.
    */
    'admin_emails' => array_values(array_filter(array_map('trim', explode(',', (string) env('ADMIN_NOTIFY_EMAILS', ''))))),

    /*
    |--------------------------------------------------------------------------
    | Login alert
    |--------------------------------------------------------------------------
    | Same user + same IP/browser theke abar login korle ei koyek ghonta email jabe na.
    */
    'login_alert'       => (bool) env('MAIL_LOGIN_ALERT', true),
    'login_alert_hours' => (int) env('MAIL_LOGIN_ALERT_HOURS', 6),
];

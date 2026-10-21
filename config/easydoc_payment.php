<?php

/*
| Manual payment settings for doctor wallet recharge.
| Change the number here (or set PAYMENT_BKASH_NUMBER in .env), then run: php artisan config:cache
*/
return [

    'bkash' => [
        'number'       => env('PAYMENT_BKASH_NUMBER', '01603600812'),
        'account_type' => 'Personal',   // doctor sends money with "Send Money"
    ],

    // A doctor cannot have more than this many requests waiting for approval
    'max_pending_requests' => 3,
];

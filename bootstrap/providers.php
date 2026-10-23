<?php

use App\Providers\AppServiceProvider;
use App\Providers\EasyDocShareServiceProvider;

return [
    AppServiceProvider::class,
    EasyDocShareServiceProvider::class,
    App\Providers\BrevoMailServiceProvider::class,
];

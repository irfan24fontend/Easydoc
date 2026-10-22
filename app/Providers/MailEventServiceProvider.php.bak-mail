<?php

namespace App\Providers;

use App\Listeners\SendLoginAlert;
use App\Models\Chamber;
use App\Models\Doctor;
use App\Observers\ChamberObserver;
use App\Observers\DoctorObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class MailEventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Doctor::observe(DoctorObserver::class);
        Chamber::observe(ChamberObserver::class);

        Event::listen(Login::class, [SendLoginAlert::class, 'onLogin']);
    }
}

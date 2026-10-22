<?php

namespace App\Observers;

use App\Models\Doctor;
use App\Services\MailNotifier;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class DoctorObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Doctor $doctor): void
    {
        if (app()->runningInConsole()) {
            return; // seeder / tinker e email jabe na
        }

        // Keu login kore doctor toiri korle (Super Admin) tahole se admin-created
        app(MailNotifier::class)->doctorRegistered($doctor, auth()->check());
    }

    public function updated(Doctor $doctor): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        if ($doctor->wasChanged('approval_status')) {
            app(MailNotifier::class)->doctorApprovalChanged($doctor);
        }
    }
}

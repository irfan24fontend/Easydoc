<?php

namespace App\Observers;

use App\Models\Chamber;
use App\Services\MailNotifier;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ChamberObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Chamber $chamber): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $mail = app(MailNotifier::class);

        if (($chamber->request_status ?? 'approved') === Chamber::REQUEST_WAITING) {
            $mail->chamberRequested($chamber);
        } else {
            $mail->chamberAdded($chamber);
        }
    }

    public function updated(Chamber $chamber): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $mail = app(MailNotifier::class);

        if ($chamber->wasChanged('request_status')) {
            if ($chamber->request_status === Chamber::REQUEST_APPROVED) {
                $mail->chamberApproved($chamber);
            } elseif ($chamber->request_status === Chamber::REQUEST_REJECTED) {
                $mail->chamberRejected($chamber);
            }

            return;
        }

        // Super Admin Active/Inactive toggle
        if ($chamber->wasChanged('status') && $chamber->request_status === Chamber::REQUEST_APPROVED) {
            $mail->chamberStatusChanged($chamber);
        }
    }
}

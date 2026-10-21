<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Doctor;
use App\Models\DoctorNotification;

class NotificationService
{
    /** A single notification for one doctor (e.g. "Recharge approved"). */
    public function notifyDoctor(
        Doctor $doctor,
        string $type,
        string $title,
        string $body,
        ?string $link = null,
        ?int $announcementId = null,
    ): DoctorNotification {
        return DoctorNotification::create([
            'doctor_id'       => $doctor->id,
            'announcement_id' => $announcementId,
            'type'            => $type,
            'title'           => $title,
            'body'            => $body,
            'link_url'        => $link,
        ]);
    }

    /**
     * Copy an announcement to every approved doctor's notification list.
     * Returns the number of doctors reached.
     */
    public function broadcast(Announcement $announcement): int
    {
        $now = now();
        $count = 0;

        Doctor::where('approval_status', Doctor::STATUS_APPROVED)
            ->select('id')
            ->chunkById(500, function ($doctors) use ($announcement, $now, &$count) {
                $rows = $doctors->map(fn ($d) => [
                    'doctor_id'       => $d->id,
                    'announcement_id' => $announcement->id,
                    'type'            => $announcement->type,
                    'title'           => $announcement->title,
                    'body'            => $announcement->body,
                    'link_url'        => $announcement->link_url,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ])->all();

                DoctorNotification::insert($rows);
                $count += count($rows);
            });

        return $count;
    }
}

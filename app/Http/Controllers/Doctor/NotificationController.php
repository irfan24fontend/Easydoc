<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Doctor;
use App\Models\DoctorNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    protected function doctor(): Doctor
    {
        return auth()->user()->doctor;
    }

    /** My notification list. */
    public function index(): View
    {
        $doctor = $this->doctor();

        $notifications = DoctorNotification::where('doctor_id', $doctor->id)
            ->orderByDesc('id')
            ->paginate(15);

        $unread = DoctorNotification::where('doctor_id', $doctor->id)->unread()->count();

        return view('doctor.notifications.index', compact('notifications', 'unread'));
    }

    /** News: everything the Super Admin has broadcast. */
    public function news(): View
    {
        $items = Announcement::latest()->paginate(10);

        return view('doctor.notifications.news', compact('items'));
    }

    /**
     * Polled by the browser every ~30 seconds (and once when a page opens).
     *
     * Returns UNREAD notifications that have not been shown yet in THIS login session.
     * So after every new login, everything the doctor has not read pops up again,
     * until the doctor opens (reads) it.
     */
    public function poll(Request $request): JsonResponse
    {
        $doctor = $this->doctor();
        $shown = (array) $request->session()->get('notif_popup_shown', []);

        $candidates = DoctorNotification::where('doctor_id', $doctor->id)
            ->unread()
            ->when(! empty($shown), fn ($q) => $q->whereNotIn('id', $shown))
            ->orderByDesc('id')
            ->get();

        // At most 5 pop-ups at a time (the newest ones, oldest of them first)
        $fresh = $candidates->take(5)->sortBy('id')->values();

        if ($candidates->isNotEmpty()) {
            // Older ones that were skipped stay unread and show again at the next login
            $request->session()->put(
                'notif_popup_shown',
                array_values(array_unique(array_merge($shown, $candidates->pluck('id')->all())))
            );

            DoctorNotification::whereIn('id', $fresh->pluck('id'))->whereNull('popup_at')->update(['popup_at' => now()]);
        }

        return response()->json([
            'unread' => DoctorNotification::where('doctor_id', $doctor->id)->unread()->count(),
            'items'  => $fresh->map(fn (DoctorNotification $n) => $this->payload($n))->all(),
        ]);
    }

    /** Mark one as read, then go to its link (or back to the list). */
    public function open(DoctorNotification $notification): RedirectResponse
    {
        $this->authorizeRow($notification);

        if (! $notification->isRead()) {
            $notification->update(['read_at' => now()]);
        }

        return $notification->link_url
            ? redirect()->away($notification->link_url)
            : redirect()->route('doctor.notifications.index');
    }

    public function markRead(DoctorNotification $notification): JsonResponse|RedirectResponse
    {
        $this->authorizeRow($notification);

        if (! $notification->isRead()) {
            $notification->update(['read_at' => now()]);
        }

        return request()->expectsJson()
            ? response()->json(['ok' => true])
            : back();
    }

    public function markAllRead(): RedirectResponse
    {
        DoctorNotification::where('doctor_id', $this->doctor()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    /** What the browser needs to show one notification in full. */
    protected function payload(DoctorNotification $n): array
    {
        return [
            'id'    => $n->id,
            'type'  => $n->type,
            'label' => $n->typeLabel(),
            'title' => $n->title,
            'body'  => $n->body,
            'time'  => $n->created_at->format('d M Y, h:i A'),
            'link'  => $n->link_url,
        ];
    }

    protected function authorizeRow(DoctorNotification $notification): void
    {
        abort_unless($notification->doctor_id === $this->doctor()->id, 404);
    }
}

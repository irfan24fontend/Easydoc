<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnnouncementRequest;
use App\Models\Announcement;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(protected NotificationService $notifications)
    {
    }

    /** Compose form + history of what was sent. */
    public function index(): View
    {
        $announcements = Announcement::withCount([
                'deliveries',
                'deliveries as read_count' => fn ($q) => $q->whereNotNull('read_at'),
            ])
            ->latest()
            ->paginate(10);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function store(AnnouncementRequest $request): RedirectResponse
    {
        $announcement = Announcement::create($request->validated() + [
            'created_by' => auth()->id(),
        ]);

        $reached = $this->notifications->broadcast($announcement);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', "Sent to {$reached} doctor" . ($reached === 1 ? '' : 's') . '. A pop-up with sound appears on their screen.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        // Also removes the copies from every doctor's notification list
        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Message deleted.');
    }
}

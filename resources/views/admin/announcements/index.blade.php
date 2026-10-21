@extends('layouts.admin')

@section('title', 'Notifications')

@push('styles')
<style>
    .an-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,.05); padding:1.4rem; }
    .an-type { display:inline-block; font-size:.68rem; font-weight:800; letter-spacing:.06em; text-transform:uppercase; padding:.22rem .6rem; border-radius:999px; }
    .an-type.feature { background:#ede9fe; color:#6d28d9; }
    .an-type.news { background:#e0f2fe; color:#0369a1; }
    .an-type.notice { background:#fef3c7; color:#b45309; }
    .an-item { border:1px solid #e2e8f0; border-radius:12px; padding:1rem 1.1rem; background:#fff; }
    .an-item + .an-item { margin-top:.75rem; }
</style>
@endpush

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold text-dark m-0">Notifications</h1>
    <p class="text-muted small m-0">Send a message to all approved doctors. It appears in their News page and notification list, and pops up on their screen with a sound.</p>
</div>

<div class="row g-4">
    {{-- Compose --}}
    <div class="col-lg-5">
        <div class="an-card">
            <h2 class="h6 fw-bold mb-3"><i class="fa-solid fa-bullhorn me-2 text-primary"></i>New message</h2>

            <form method="POST" action="{{ route('admin.announcements.store') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold small" for="type">Type</label>
                    <select id="type" name="type" class="form-select @error('type') is-invalid @enderror">
                        @foreach(\App\Models\Announcement::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', 'feature') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small" for="title">Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" maxlength="160" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Favorite Medicines is now live">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small" for="body">Message</label>
                    <textarea id="body" name="body" rows="5" maxlength="2000" class="form-control @error('body') is-invalid @enderror" placeholder="Write what doctors should know">{{ old('body') }}</textarea>
                    @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small" for="link_url">Link (optional)</label>
                    <input type="text" id="link_url" name="link_url" value="{{ old('link_url') }}" class="form-control @error('link_url') is-invalid @enderror" placeholder="/doctor/favorites or https://...">
                    @error('link_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Doctors reach this page when they press "View" on the pop-up.</div>
                </div>

                <button type="submit" class="btn btn-primary px-4 fw-semibold" onclick="return confirm('Send this message to all approved doctors now?')">
                    <i class="fa-solid fa-paper-plane me-1"></i> Send to all doctors
                </button>
            </form>
        </div>
    </div>

    {{-- History --}}
    <div class="col-lg-7">
        <div class="an-card">
            <h2 class="h6 fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Sent messages</h2>

            @forelse($announcements as $a)
                <div class="an-item">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <span class="an-type {{ $a->type }}">{{ $a->typeLabel() }}</span>
                            <div class="fw-bold mt-1">{{ $a->title }}</div>
                        </div>
                        <form method="POST" action="{{ route('admin.announcements.destroy', $a->id) }}" class="m-0" onsubmit="return confirm('Delete this message? It disappears from every doctor.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light border text-danger" aria-label="Delete"><i class="fa-solid fa-trash-can"></i></button>
                        </form>
                    </div>
                    <div class="small text-secondary mt-1" style="white-space:pre-line">{{ \Illuminate\Support\Str::limit($a->body, 240) }}</div>
                    <div class="small text-muted mt-2">
                        {{ $a->created_at->format('d M Y, h:i A') }}
                        &middot; Sent to {{ $a->deliveries_count }} doctor{{ $a->deliveries_count === 1 ? '' : 's' }}
                        &middot; Read by {{ $a->read_count }}
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5"><i class="fa-regular fa-paper-plane fs-3 d-block mb-2"></i>No message sent yet.</div>
            @endforelse

            <div class="mt-3">{{ $announcements->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</div>
@endsection

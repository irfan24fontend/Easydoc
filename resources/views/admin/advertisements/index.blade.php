@extends('layouts.admin')

@section('title', 'Medicine Ads')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="text-uppercase fw-bold small" style="color:#0c8c84;letter-spacing:.08em;">Sponsored content</div>
        <h1 class="h3 fw-bold mb-1">Medicine ads</h1>
        <p class="text-secondary mb-0">Doctors see active ads one at a time, in the order they were added.</p>
    </div>
    <a href="{{ route('admin.advertisements.create') }}" class="btn btn-success px-3" style="background:#0c8c84;border-color:#0c8c84;">
        <i class="fa-solid fa-plus me-1"></i> Add medicine ad
    </a>
</div>

<div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-lg">
                <h2 class="h6 fw-bold mb-1"><i class="fa-regular fa-clock me-2" style="color:#0c8c84;"></i>Display interval</h2>
                <p class="text-secondary small mb-0">Wait this many minutes after login and between ads. Each ad stays on screen for at least 10 seconds.</p>
            </div>
            <div class="col-lg-auto">
                <form method="POST" action="{{ route('admin.advertisements.settings.update') }}" class="d-flex align-items-end gap-2">
                    @csrf
                    <div>
                        <label for="interval_minutes" class="form-label small fw-semibold mb-1">Minutes between ads</label>
                        <input id="interval_minutes" type="number" name="interval_minutes" min="1" max="1440" required
                               value="{{ old('interval_minutes', $settings->interval_minutes) }}" class="form-control" style="width:150px;">
                        @error('interval_minutes') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <button class="btn btn-dark" type="submit">Save interval</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr><th class="ps-4">Rotation order</th><th>Medicine</th><th>Status</th><th>Impressions</th><th class="text-end pe-4">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($advertisements as $advertisement)
                    <tr>
                        <td class="ps-4 text-secondary">{{ $advertisements->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ asset('storage/'.$advertisement->image_path) }}" alt="" style="width:72px;height:52px;object-fit:cover;border-radius:9px;background:#f1f5f9;">
                                <div><div class="fw-bold">{{ $advertisement->medicine_name }}</div><div class="small text-secondary">Added {{ $advertisement->created_at->format('M j, Y') }}</div></div>
                            </div>
                        </td>
                        <td><span class="badge rounded-pill {{ $advertisement->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $advertisement->is_active ? 'Active' : 'Paused' }}</span></td>
                        <td><span class="fw-bold">{{ number_format($advertisement->impressions) }}</span></td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.advertisements.edit', $advertisement) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('admin.advertisements.destroy', $advertisement) }}" class="d-inline" onsubmit="return confirm('Delete this medicine ad?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5 text-secondary">No ads yet. Add an image and medicine details to get started.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($advertisements->hasPages()) <div class="p-3">{{ $advertisements->links() }}</div> @endif
</div>
@endsection

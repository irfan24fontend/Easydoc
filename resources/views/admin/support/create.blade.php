@extends('layouts.admin')

@section('title', 'Message a doctor')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.support.index') }}" class="small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> All support chats</a>
    <h1 class="h3 fw-bold mt-2 mb-1">Message a doctor</h1>
    <div class="text-muted">Choose a doctor to open or continue their support chat.</div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-3 p-md-4">
        <form method="POST" action="{{ route('admin.support.start') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="supportDoctor" class="form-label fw-semibold">Doctor</label>
                <select id="supportDoctor" name="doctor_id" class="form-select" required>
                    <option value="">Select a doctor</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                            {{ $doctor->displayName() }}{{ $doctor->user?->email ? ' · ' . $doctor->user->email : '' }}
                        </option>
                    @endforeach
                </select>
                @error('doctor_id')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="supportMessage" class="form-label fw-semibold">Message</label>
                <textarea id="supportMessage" name="body" rows="4" maxlength="2000" class="form-control" placeholder="Write a message to the doctor…">{{ old('body') }}</textarea>
                @error('body')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <label class="btn btn-outline-secondary mb-0" for="supportImage"><i class="fa-regular fa-image me-1"></i> Attach image</label>
                <input id="supportImage" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="visually-hidden">
                <span class="small text-muted" id="supportImageName">JPG, PNG, WEBP or GIF · max 5 MB</span>
                <button class="btn btn-success ms-sm-auto" type="submit"><i class="fa-solid fa-paper-plane me-1"></i> Send to doctor</button>
            </div>
            @error('image')<div class="small text-danger mt-2">{{ $message }}</div>@enderror
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('supportImage')?.addEventListener('change', function () {
    document.getElementById('supportImageName').textContent = this.files[0]?.name || 'JPG, PNG, WEBP or GIF · max 5 MB';
});
</script>
@endpush

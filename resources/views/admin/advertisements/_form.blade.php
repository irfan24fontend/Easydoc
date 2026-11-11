@if($errors->any())
    <div class="alert alert-danger"><strong>Please fix these items:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="card border-0 shadow-sm mb-3" style="border-radius:16px;">
    <div class="card-body p-4">
        <div class="mb-3">
            <label for="medicine_name" class="form-label fw-semibold">Medicine / product name</label>
            <input id="medicine_name" name="medicine_name" maxlength="160" required value="{{ old('medicine_name', $advertisement->medicine_name ?? '') }}" class="form-control @error('medicine_name') is-invalid @enderror" placeholder="For example, product or brand name">
            @error('medicine_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="medicine_details" class="form-label fw-semibold">Medicine details</label>
            <textarea id="medicine_details" name="medicine_details" rows="6" maxlength="4000" required class="form-control @error('medicine_details') is-invalid @enderror" placeholder="Information doctors can read from the ad popup">{{ old('medicine_details', $advertisement->medicine_details ?? '') }}</textarea>
            <div class="form-text">Doctors can open these details using “Read medicine details” in the popup.</div>
            @error('medicine_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        @if(isset($advertisement) && $advertisement->image_path)
            <div class="mb-3">
                <div class="form-label fw-semibold">Current image</div>
                <img src="{{ asset('storage/'.$advertisement->image_path) }}" alt="Current ad image" style="max-width:320px;max-height:220px;object-fit:contain;border-radius:12px;background:#f8fafc;">
            </div>
        @endif

        <div class="mb-4">
            <label for="image" class="form-label fw-semibold">Ad image @if(!isset($advertisement))<span class="text-danger">*</span>@endif</label>
            <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="form-control @error('image') is-invalid @enderror" @if(!isset($advertisement)) required @endif>
            <div class="form-text">JPG, PNG or WebP, up to 5 MB. The image is shown in the doctor popup.</div>
            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input id="is_active" name="is_active" type="checkbox" value="1" class="form-check-input" role="switch" @checked(old('is_active', $advertisement->is_active ?? true))>
            <label for="is_active" class="form-check-label fw-semibold">Show this ad to doctors</label>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between gap-2">
    <a href="{{ route('admin.advertisements.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-success px-4" style="background:#0c8c84;border-color:#0c8c84;">{{ $submitLabel }}</button>
</div>

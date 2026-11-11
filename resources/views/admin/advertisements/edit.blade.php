@extends('layouts.admin')

@section('title', 'Edit Medicine Ad')

@section('content')
<div class="mx-auto" style="max-width:820px;">
    <div class="mb-4">
        <a href="{{ route('admin.advertisements.index') }}" class="text-decoration-none text-secondary small"><i class="fa-solid fa-arrow-left me-1"></i> Medicine ads</a>
        <h1 class="h3 fw-bold mt-2 mb-1">Edit medicine ad</h1>
        <p class="text-secondary mb-0">Update the image, details or display status.</p>
    </div>
    <form method="POST" action="{{ route('admin.advertisements.update', $advertisement) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('admin.advertisements._form', ['submitLabel' => 'Save changes'])
    </form>
</div>
@endsection

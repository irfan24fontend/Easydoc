@extends('layouts.admin')

@section('title', 'Add Medicine Ad')

@section('content')
<div class="mx-auto" style="max-width:820px;">
    <div class="mb-4">
        <a href="{{ route('admin.advertisements.index') }}" class="text-decoration-none text-secondary small"><i class="fa-solid fa-arrow-left me-1"></i> Medicine ads</a>
        <h1 class="h3 fw-bold mt-2 mb-1">Add medicine ad</h1>
        <p class="text-secondary mb-0">Upload the image and add the information doctors can read.</p>
    </div>
    <form method="POST" action="{{ route('admin.advertisements.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.advertisements._form', ['submitLabel' => 'Add ad'])
    </form>
</div>
@endsection

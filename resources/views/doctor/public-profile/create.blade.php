@extends('layouts.doctor')

@section('title', 'Create Public Profile')

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold m-0">Create your public profile</h1>
    <p class="text-muted small m-0">A professional page with your own link that you can share with anyone. Fields marked <span class="text-danger">*</span> are required.</p>
</div>

<div style="max-width: 920px;">
    @include('doctor.public-profile._form', ['doctor' => $doctor, 'profile' => null, 'suggestedSlug' => $suggestedSlug])
</div>
@endsection

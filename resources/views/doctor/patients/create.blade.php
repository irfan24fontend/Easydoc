@extends('layouts.doctor')

@section('title', 'নতুন রোগী')

@push('styles')
<style>
    .glass-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(15,23,42,.04); }
</style>
@endpush

@section('content')
<div class="py-2">
    <div class="mb-4">
        <h1 class="h4 fw-bold text-dark m-0">নতুন রোগী</h1>
        <p class="text-muted small m-0">রোগীর প্রোফাইল তৈরি করুন। Patient ID নিজে থেকে তৈরি হবে।</p>
    </div>

    @include('doctor.patients.form', ['patient' => new \App\Models\Patient()])
</div>
@endsection
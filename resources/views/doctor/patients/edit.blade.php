@extends('layouts.doctor')

@section('title', 'রোগীর তথ্য সম্পাদনা')

@push('styles')
<style>
    .glass-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(15,23,42,.04); }
</style>
@endpush

@section('content')
<div class="py-2">
    <div class="mb-4">
        <h1 class="h4 fw-bold text-dark m-0">রোগীর তথ্য সম্পাদনা
            <span class="ms-2 align-middle" style="font-family:ui-monospace,Menlo,monospace;font-size:.8rem;background:#eef2ff;color:#4338ca;border:1px solid #e0e7ff;border-radius:6px;padding:.15rem .5rem;">{{ $patient->patient_code }}</span>
        </h1>
        <p class="text-muted small m-0">এখানে বদলালে নতুন প্রেসক্রিপশনে নতুন তথ্য আসবে। আগের প্রেসক্রিপশন বদলাবে না।</p>
    </div>

    @include('doctor.patients.form', ['patient' => $patient])
</div>
@endsection
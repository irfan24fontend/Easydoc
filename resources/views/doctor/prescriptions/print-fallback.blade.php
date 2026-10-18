@extends('layouts.doctor')

@section('title', 'Prescription')

@section('content')
<div class="alert alert-warning">
    <strong>⚠ কোনো Prescription Template অ্যাসাইন করা নেই এই চেম্বারে।</strong><br>
    সুপার এডমিনের সাথে যোগাযোগ করে আপনার চেম্বারে একটা টেমপ্লেট সেট করে নিন — তারপর এই প্রেসক্রিপশনটা প্রিন্ট করা যাবে।
</div>
<p>Prescription ID: #{{ $prescription->id }} — সেভ হয়ে গেছে, টেমপ্লেট সেট হলে পরে প্রিন্ট করতে পারবেন।</p>
<a href="{{ route('doctor.dashboard') }}" class="btn btn-secondary">← Dashboard</a>
@endsection
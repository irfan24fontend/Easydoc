@extends('layouts.doctor')

@section('title', 'Create Public Profile')

@section('content')
<div class="pp">
    <div class="pp-hero">
        <div class="pp-hero-ico"><i class="fa-solid fa-globe"></i></div>
        <div class="flex-grow-1">
            <h1>Create your public profile</h1>
            <p>নিজের নামে একটি শেয়ারযোগ্য পেজ তৈরি করুন — রোগীরা লিংকে ঢুকেই আপনার পরিচয়, চেম্বার ও সময় দেখতে পাবে। <span class="req-w">*</span> চিহ্নিত ঘরগুলো আবশ্যক।</p>
            <div class="pp-steps">
                <span class="on"><b>1</b> তথ্য দিন</span>
                <span><b>2</b> লাইভ প্রিভিউ দেখুন</span>
                <span><b>3</b> পাবলিশ ও শেয়ার</span>
            </div>
        </div>
    </div>

    @include('doctor.public-profile._form', ['doctor' => $doctor, 'profile' => null, 'suggestedSlug' => $suggestedSlug])
</div>
@endsection
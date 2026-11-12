@extends('layouts.assistant')

@section('title', 'Prescription')

@section('content')
<div class="cardx p-4">
    <div class="alert alert-warning mb-3">
        This chamber does not have a prescription template assigned, so this prescription cannot be printed yet.
        Ask the doctor or administrator to assign the chamber template.
    </div>
    <p class="mb-3">Prescription #{{ $prescription->id }} for {{ $prescription->patient->name ?? 'patient' }}.</p>
    <a href="{{ route('assistant.serials.index', ['chamber_id' => $prescription->chamber_id]) }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to patient search
    </a>
</div>
@endsection

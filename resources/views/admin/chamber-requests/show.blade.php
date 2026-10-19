@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-5xl">

    <div class="mb-5 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.chamber-requests.index') }}" class="text-sm text-indigo-600 hover:underline">Back to requests</a>
            <h1 class="mt-1 text-xl font-semibold text-slate-900">{{ $chamber->chamber_name }}</h1>
        </div>
        @include('partials.chamber-status-badge', ['status' => $chamber->display_status])
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-5">

        {{-- Details --}}
        <div class="space-y-4 lg:col-span-2">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-500">Doctor</dt>
                        <dd class="text-slate-900">{{ $chamber->doctor->name ?? ($chamber->doctor->user->name ?? 'Doctor #'.$chamber->doctor_id) }}</dd></div>
                    <div><dt class="text-slate-500">Hospital / clinic</dt><dd class="text-slate-900">{{ $chamber->hospital_name }}</dd></div>
                    <div><dt class="text-slate-500">Phone</dt><dd class="text-slate-900">{{ $chamber->phone }}</dd></div>
                    @if($chamber->email)
                        <div><dt class="text-slate-500">Email</dt><dd class="text-slate-900">{{ $chamber->email }}</dd></div>
                    @endif
                    <div><dt class="text-slate-500">Address</dt><dd class="text-slate-900">{{ $chamber->address }}</dd></div>
                    <div><dt class="text-slate-500">Visiting days</dt><dd class="text-slate-900">{{ $chamber->visiting_days }}</dd></div>
                    <div><dt class="text-slate-500">Visiting time</dt><dd class="text-slate-900">{{ $chamber->visiting_time }}</dd></div>
                    <div><dt class="text-slate-500">Requested</dt>
                        <dd class="text-slate-900">{{ optional($chamber->requested_at)->format('d M Y, h:i A') ?? '-' }}</dd></div>
                    @if($chamber->template)
                        <div><dt class="text-slate-500">Assigned template</dt><dd class="text-slate-900">{{ $chamber->template->name }}</dd></div>
                    @endif
                    @if($chamber->reject_reason)
                        <div><dt class="text-slate-500">Reject reason</dt><dd class="text-rose-700">{{ $chamber->reject_reason }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Pad image + actions --}}
        <div class="space-y-4 lg:col-span-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="mb-3 text-sm font-medium text-slate-700">Prescription pad</p>
                @if($chamber->pad_image)
                    <a href="{{ asset('storage/'.$chamber->pad_image) }}" target="_blank" rel="noopener">
                        <img src="{{ asset('storage/'.$chamber->pad_image) }}" alt="Prescription pad" class="max-h-[32rem] w-full rounded-lg border border-slate-200 object-contain">
                    </a>
                @else
                    <p class="text-sm text-slate-500">No pad image uploaded.</p>
                @endif
            </div>

            @if($chamber->request_status === 'waiting_request')
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <p class="text-sm font-medium text-slate-700">Assign template and activate</p>
                    <p class="mt-1 text-xs text-slate-500">
                        Create a template from the pad image first, then pick it here.
                        @if(Route::has('admin.prescription-templates.create'))
                            <a href="{{ route('admin.prescription-templates.create') }}" class="text-indigo-600 hover:underline">Create template</a>
                        @endif
                    </p>

                    <form method="POST" action="{{ route('admin.chamber-requests.approve', $chamber) }}" class="mt-3 flex flex-wrap items-end gap-3">
                        @csrf
                        <div class="min-w-[14rem] flex-1">
                            <select name="prescription_template_id" class="w-full rounded-lg border-slate-300 text-sm" required>
                                <option value="">Select template</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" @selected(old('prescription_template_id') == $template->id)>{{ $template->name }} ({{ $template->paper_size }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Activate chamber</button>
                    </form>

                    <form method="POST" action="{{ route('admin.chamber-requests.reject', $chamber) }}" class="mt-5 border-t border-slate-100 pt-4">
                        @csrf
                        <label class="mb-1 block text-sm font-medium text-slate-700">Reject request</label>
                        <p class="mb-2 text-xs text-slate-500">৳{{ $fee }} will be refunded to the doctor's wallet.</p>
                        <div class="flex flex-wrap gap-3">
                            <input type="text" name="reject_reason" value="{{ old('reject_reason') }}" placeholder="Reason shown to the doctor" class="min-w-[14rem] flex-1 rounded-lg border-slate-300 text-sm" required>
                            <button class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700">Reject and refund</button>
                        </div>
                    </form>
                </div>

            @elseif($chamber->request_status === 'approved')
                <div class="rounded-xl border border-slate-200 bg-white p-5">
                    <form method="POST" action="{{ route('admin.chamber-requests.toggle', $chamber) }}">
                        @csrf @method('PATCH')
                        <button class="rounded-lg px-4 py-2 text-sm font-medium text-white {{ $chamber->status ? 'bg-slate-600 hover:bg-slate-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                            {{ $chamber->status ? 'Set inactive' : 'Set active' }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-6xl">

    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Chamber requests</h1>
        <a href="{{ route('admin.chambers.index') }}" class="text-sm text-indigo-600 hover:underline">All chambers</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    {{-- Filter tabs --}}
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach(['waiting_request' => 'Waiting request', 'active' => 'Active', 'inactive' => 'Inactive', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.chamber-requests.index', ['filter' => $key]) }}"
               class="rounded-lg px-3 py-1.5 text-sm {{ $filter === $key ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">
                {{ $label }} <span class="{{ $filter === $key ? 'text-indigo-200' : 'text-slate-400' }}">{{ $counts[$key] }}</span>
            </a>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Doctor</th>
                    <th class="px-4 py-3 font-medium">Chamber</th>
                    <th class="px-4 py-3 font-medium">Requested</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($chambers as $chamber)
                    <tr>
                        <td class="px-4 py-3 text-slate-900">
                            {{ $chamber->doctor->name ?? ($chamber->doctor->user->name ?? 'Doctor #'.$chamber->doctor_id) }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-slate-900">{{ $chamber->chamber_name }}</p>
                            <p class="text-xs text-slate-500">{{ $chamber->hospital_name }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ optional($chamber->requested_at)->format('d M Y, h:i A') ?? '-' }}</td>
                        <td class="px-4 py-3">@include('partials.chamber-status-badge', ['status' => $chamber->display_status])</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.chamber-requests.show', $chamber) }}" class="text-indigo-600 hover:underline">
                                {{ $chamber->request_status === 'waiting_request' ? 'Review' : 'View' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No chambers in this view.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $chambers->links() }}</div>
</div>
@endsection

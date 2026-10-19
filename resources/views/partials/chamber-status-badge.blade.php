@php
    $map = [
        'active'          => ['Active',          'bg-emerald-50 text-emerald-700 ring-emerald-600/20'],
        'inactive'        => ['Inactive',        'bg-slate-100 text-slate-600 ring-slate-500/20'],
        'waiting_request' => ['Waiting request', 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        'rejected'        => ['Rejected',        'bg-rose-50 text-rose-700 ring-rose-600/20'],
    ];
    [$label, $classes] = $map[$status] ?? [$status, 'bg-slate-100 text-slate-600 ring-slate-500/20'];
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $classes }}">{{ $label }}</span>

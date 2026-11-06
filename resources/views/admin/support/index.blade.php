@extends('layouts.admin')

@section('title', 'Doctor Support')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <div class="text-uppercase small fw-bold text-success mb-1">Support desk</div>
        <h1 class="h3 fw-bold mb-1">Doctor live chat</h1>
        <div class="text-muted">Reply to doctors who need help with EasyDoc.</div>
    </div>
    <div class="btn-group" role="group" aria-label="Filter conversations">
        <a class="btn {{ $status === 'open' ? 'btn-success' : 'btn-outline-secondary' }}" href="{{ route('admin.support.index', ['status' => 'open']) }}">Open</a>
        <a class="btn {{ $status === 'closed' ? 'btn-success' : 'btn-outline-secondary' }}" href="{{ route('admin.support.index', ['status' => 'closed']) }}">Closed</a>
        <a class="btn {{ $status === 'all' ? 'btn-success' : 'btn-outline-secondary' }}" href="{{ route('admin.support.index', ['status' => 'all']) }}">All</a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Doctor</th>
                    <th>Issue / latest message</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th class="text-end pe-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($conversations as $conversation)
                    @php
                        $doctorName = $conversation->doctor?->displayName() ?? 'Doctor';
                        $latest = $conversation->latestMessage;
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">{{ $doctorName }}</div>
                            <div class="small text-muted">{{ $conversation->doctor?->user?->email }}</div>
                        </td>
                        <td style="min-width:240px;max-width:460px">
                            <div class="fw-semibold text-truncate">{{ $conversation->subject }}</div>
                            <div class="small text-muted text-truncate">{{ $latest?->body ?? 'No messages yet' }}</div>
                        </td>
                        <td>
                            <span class="badge rounded-pill {{ $conversation->status === 'open' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ ucfirst($conversation->status) }}</span>
                            @if($conversation->unread_count > 0)
                                <span class="badge rounded-pill text-bg-warning ms-1">{{ $conversation->unread_count }} new</span>
                            @endif
                        </td>
                        <td class="small text-muted text-nowrap">{{ $conversation->last_message_at?->timezone(config('easydoc.timezone', 'Asia/Dhaka'))->format('d M Y, h:i A') ?? '—' }}</td>
                        <td class="text-end pe-4">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('admin.support.show', $conversation) }}">Open chat</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">No {{ $status === 'all' ? '' : $status }} support conversations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($conversations->hasPages())
        <div class="card-footer bg-white border-0 px-4">{{ $conversations->links() }}</div>
    @endif
</div>
@endsection

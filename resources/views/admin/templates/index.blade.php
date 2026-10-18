@extends('layouts.admin')

@section('title', 'Prescription Templates')

@section('content')
<style>
    .template-workspace { 
        background-color: #f8fafc; 
        min-height: 100vh; 
    }
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .saas-table th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
        background-color: #f1f5f9;
        border-bottom: 2px solid #e2e8f0;
        padding: 1rem 1.25rem;
    }
    .saas-table td {
        font-size: 0.875rem;
        color: #334155;
        padding: 1rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
    }
    .saas-table tr:hover td { 
        background-color: #f8fafc; 
    }
    .btn-action-text {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        padding: 0.4rem 0.75rem;
        border-radius: 6px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease;
    }
    .btn-action-text.edit { 
        color: #b45309; 
        background-color: #fef3c7; 
        border: 1px solid #fde68a; 
    }
    .btn-action-text.edit:hover { 
        background-color: #d97706; 
        color: #ffffff; 
    }
    .btn-action-text.delete { 
        color: #dc2626; 
        background-color: #fee2e2; 
        border: 1px solid #fecaca; 
    }
    .btn-action-text.delete:hover { 
        background-color: #dc2626; 
        color: #ffffff; 
    }
</style>

<div class="template-workspace py-4">
    <div class="container-fluid px-4">

        <!-- Header Panel -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 fw-bold text-dark m-0">Prescription Templates</h1>
                <p class="text-muted small m-0">Manage layout markup, stylesheets, and print definitions.</p>
            </div>
            <a href="{{ route('admin.templates.create') }}" class="btn btn-primary px-4 fw-semibold" style="background-color: #4f46e5; border-color: #4f46e5;">
                + Create Template
            </a>
        </div>

        <!-- Templates Data Grid -->
        <div class="glass-card overflow-hidden">
            <div class="table-responsive">
                <table class="table saas-table m-0 border-0">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>Template Name</th>
                            <th>Paper Size</th>
                            <th>Status</th>
                            <th width="180" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td class="text-muted fw-bold">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="fw-bold text-dark">
                                    {{ $template->name }}
                                </td>

                                <td>
                                    <span class="font-monospace fw-bold px-2 py-1 rounded" style="font-size: 0.85rem; background-color: #f1f5f9; color: #475569;">
                                        {{ $template->paper_size }}
                                    </span>
                                </td>

                                <td>
                                    @if($template->status)
                                        <span class="badge px-2.5 py-1.5 rounded-pill" style="background-color: #d1fae5; color: #065f46; font-size: 0.75rem; font-weight: 600;">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge px-2.5 py-1.5 rounded-pill" style="background-color: #fee2e2; color: #991b1b; font-size: 0.75rem; font-weight: 600;">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.templates.edit', $template->id) }}" class="btn-action-text edit">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('admin.templates.destroy', $template->id) }}" class="d-inline m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Delete this template?')" class="btn-action-text delete border-0">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted fw-medium">
                                    No Prescription Template Found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($templates, 'hasPages') && $templates->hasPages())
                <div class="px-4 py-3 border-top bg-light">
                    {{ $templates->links() }}
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
@extends('admin.layouts.app')

@section('page-title', 'Plans')

@section('content')
    <div class="glass-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="h5 mb-1">Plans & Packages</h2>
                <p class="text-secondary mb-0">Manage category-based packages, pricing, and inclusions.</p>
            </div>
            <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">Add Plan</a>
        </div>
        <form method="GET" class="filter-grid mt-4">
            <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search title">
            <select class="form-select" name="status">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <button class="btn btn-outline-primary">Filter</button>
        </form>
    </div>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Title</th><th>Category</th><th>Price</th><th>Duration</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td class="fw-semibold">{{ $plan->title }}</td>
                            <td>{{ $plan->category?->name }}</td>
                            <td>₹{{ number_format($plan->price, 2) }}</td>
                            <td>{{ $plan->duration }}</td>
                            <td><x-admin.status-badge :value="$plan->status" /></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.plans.show', $plan) }}">View</a>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.plans.edit', $plan) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('admin.plans.destroy', $plan) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this plan?')">Delete</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-secondary">No plans found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $plans->links() }}</div>
    </div>
@endsection

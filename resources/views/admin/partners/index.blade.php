@extends('admin.layouts.app')

@section('page-title', 'Partners')

@section('content')
    <div class="glass-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="h5 mb-1">Partner Management</h2>
                <p class="text-secondary mb-0">Create, update, and monitor shoot partners.</p>
            </div>
            <a href="{{ route('admin.partners.create') }}" class="btn btn-primary">Add Partner</a>
        </div>
        <form method="GET" class="filter-grid mt-4">
            <input class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, phone, email">
            <select class="form-select" name="status">
                <option value="">All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
            </select>
            <button class="btn btn-outline-primary">Filter</button>
        </form>
    </div>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Status</th><th>Assigned Bookings</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($partners as $partner)
                        <tr>
                            <td class="fw-semibold">{{ $partner->name }}</td>
                            <td>{{ $partner->phone }}</td>
                            <td>{{ $partner->email ?: 'N/A' }}</td>
                            <td><x-admin.status-badge :value="$partner->status" /></td>
                            <td>{{ $partner->assigned_bookings_count }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.partners.show', $partner) }}">View</a>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.partners.edit', $partner) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('admin.partners.destroy', $partner) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this partner?')">Delete</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-secondary">No partners found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $partners->links() }}</div>
    </div>
@endsection

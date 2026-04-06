@extends('admin.layouts.app')

@section('page-title', 'Users')

@section('content')
    <div class="glass-card p-4 mb-4">
        <h2 class="h5 mb-3">User Management</h2>
        <form method="GET" class="filter-grid">
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
                <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Status</th><th>Bookings</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>{{ $user->email ?: 'N/A' }}</td>
                            <td><x-admin.status-badge :value="$user->status" /></td>
                            <td>{{ $user->bookings()->count() }}</td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.show', $user) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-secondary">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $users->links() }}</div>
    </div>
@endsection

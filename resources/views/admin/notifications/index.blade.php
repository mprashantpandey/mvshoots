@extends('admin.layouts.app')

@section('page-title', 'Notifications')

@section('content')
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="glass-card p-4 h-100">
                <h2 class="h5 mb-3">Send Manual Notification</h2>
                <form method="POST" action="{{ route('admin.notifications.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Target User Type</label>
                        <select class="form-select" name="user_type">
                            <option value="user">User</option>
                            <option value="partner">Partner</option>
                            <option value="owner">Owner</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Target User ID</label><input class="form-control" name="user_id" required></div>
                    <div class="mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
                    <div class="mb-3"><label class="form-label">Type</label><input class="form-control" name="type" placeholder="manual_notification" required></div>
                    <div class="mb-3"><label class="form-label">Reference ID</label><input class="form-control" name="reference_id"></div>
                    <div class="mb-3"><label class="form-label">Message</label><textarea class="form-control" name="body" required></textarea></div>
                    <button class="btn btn-primary w-100">Send Notification</button>
                </form>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="glass-card p-4 mb-4">
                <form method="GET" class="filter-grid">
                    <input class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search title or body">
                    <select class="form-select" name="user_type">
                        <option value="">All targets</option>
                        <option value="user" @selected(($filters['user_type'] ?? '') === 'user')>User</option>
                        <option value="partner" @selected(($filters['user_type'] ?? '') === 'partner')>Partner</option>
                        <option value="owner" @selected(($filters['user_type'] ?? '') === 'owner')>Owner</option>
                        <option value="admin" @selected(($filters['user_type'] ?? '') === 'admin')>Admin</option>
                    </select>
                    <input class="form-control" name="type" value="{{ $filters['type'] ?? '' }}" placeholder="Type">
                    <button class="btn btn-outline-primary">Filter</button>
                </form>
            </div>
            <div class="table-card">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Title</th><th>Target</th><th>Type</th><th>Read</th><th>Created</th></tr></thead>
                        <tbody>
                            @forelse($notifications as $notification)
                                <tr>
                                    <td><div class="fw-semibold">{{ $notification->title }}</div><div class="small text-secondary">{{ str($notification->body)->limit(70) }}</div></td>
                                    <td>{{ ucfirst($notification->user_type) }} #{{ $notification->user_id }}</td>
                                    <td>{{ $notification->type }}</td>
                                    <td><x-admin.status-badge :value="$notification->is_read ? 'active' : 'pending'" /></td>
                                    <td>{{ $notification->created_at }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-5 text-secondary">No notifications logged yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $notifications->links() }}</div>
            </div>
        </div>
    </div>
@endsection

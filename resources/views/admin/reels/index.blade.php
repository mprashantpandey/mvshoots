@extends('admin.layouts.app')

@section('page-title', 'Reels')

@section('content')
    <div class="glass-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="h5 mb-1">Reels Management</h2>
                <p class="text-secondary mb-0">Centralized showcase reel library for the platform.</p>
            </div>
            <a href="{{ route('admin.reels.create') }}" class="btn btn-primary">Add Reel</a>
        </div>
        <form method="GET" class="filter-grid mt-4">
            <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search reel title">
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
                <thead><tr><th>Thumbnail</th><th>Title</th><th>Category</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($reels as $reel)
                        <tr>
                            <td>
                                @if($reel->thumbnail)
                                    <img src="{{ str($reel->thumbnail)->startsWith('http') ? $reel->thumbnail : asset('storage/' . $reel->thumbnail) }}" width="70" height="52" class="rounded-4 object-fit-cover">
                                @else
                                    <div class="avatar-circle"><i class="bi bi-play-btn"></i></div>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $reel->title }}</td>
                            <td>{{ $reel->category?->name ?? 'Unassigned' }}</td>
                            <td><x-admin.status-badge :value="$reel->status" /></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.reels.show', $reel) }}">View</a>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.reels.edit', $reel) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('admin.reels.destroy', $reel) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this reel?')">Delete</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5 text-secondary">No reels found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $reels->links() }}</div>
    </div>
@endsection

@extends('admin.layouts.app')

@section('page-title', 'Owners')

@section('content')
    <div class="glass-card p-4 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="h5 mb-1">Owner Management</h2>
            <p class="text-secondary mb-0">Manage secure owner accounts and access.</p>
        </div>
        <a href="{{ route('admin.owners.create') }}" class="btn btn-primary">Add Owner</a>
    </div>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Name</th><th>Email</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($owners as $owner)
                        <tr>
                            <td class="fw-semibold">{{ $owner->name }}</td>
                            <td>{{ $owner->email }}</td>
                            <td><x-admin.status-badge :value="$owner->status" /></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.owners.show', $owner) }}">View</a>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.owners.edit', $owner) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('admin.owners.destroy', $owner) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this owner?')">Delete</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-5 text-secondary">No owners found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $owners->links() }}</div>
    </div>
@endsection

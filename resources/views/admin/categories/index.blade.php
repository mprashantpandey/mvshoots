@extends('admin.layouts.app')

@section('page-title', 'Categories')

@section('content')
    <div class="glass-card p-4 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
            <div>
                <h2 class="h5 mb-1">Category Management</h2>
                <p class="text-secondary mb-0">Create and maintain public booking categories.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add Category</a>
        </div>
        <form method="GET" class="filter-grid mt-4">
            <input class="form-control" type="text" name="search" placeholder="Search category" value="{{ request('search') }}">
            <select class="form-select" name="status">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <button class="btn btn-outline-primary" type="submit">Filter</button>
        </form>
    </div>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Image</th><th>Name</th><th>Description</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>
                                @if($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" width="56" height="56" class="rounded-4 object-fit-cover">
                                @else
                                    <div class="avatar-circle">{{ strtoupper(substr($category->name, 0, 1)) }}</div>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td class="text-secondary">{{ str($category->description)->limit(70) }}</td>
                            <td><x-admin.status-badge :value="$category->status" /></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-light" href="{{ route('admin.categories.show', $category) }}">View</a>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5 text-secondary">No categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $categories->links() }}</div>
    </div>
@endsection

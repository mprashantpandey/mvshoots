@extends('admin.layouts.app')

@section('page-title', $category->exists ? 'Edit Category' : 'Create Category')

@section('content')
    <div class="glass-card p-4">
        <h2 class="h5 mb-4">{{ $category->exists ? 'Update category' : 'Add new category' }}</h2>
        <form method="POST" enctype="multipart/form-data" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
            @csrf
            @if($category->exists) @method('PUT') @endif
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input class="form-control" name="name" value="{{ old('name', $category->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="active" @selected(old('status', $category->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $category->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description">{{ old('description', $category->description) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Image</label>
                    <input class="form-control" type="file" name="image">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Save Category</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.categories.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection

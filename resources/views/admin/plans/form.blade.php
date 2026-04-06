@extends('admin.layouts.app')

@section('page-title', $plan->exists ? 'Edit Plan' : 'Create Plan')

@section('content')
    <div class="glass-card p-4">
        <form method="POST" action="{{ $plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store') }}">
            @csrf
            @if($plan->exists) @method('PUT') @endif
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $plan->category_id) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="active" @selected(old('status', $plan->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $plan->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input class="form-control" name="title" value="{{ old('title', $plan->title) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Price</label>
                    <input class="form-control" type="number" step="0.01" name="price" value="{{ old('price', $plan->price) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Duration</label>
                    <input class="form-control" name="duration" value="{{ old('duration', $plan->duration) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description">{{ old('description', $plan->description) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Inclusions (one per line)</label>
                    <textarea class="form-control" name="inclusions">{{ old('inclusions', is_array($plan->inclusions) ? implode("\n", $plan->inclusions) : '') }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Save Plan</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.plans.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection

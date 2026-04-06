@extends('admin.layouts.app')

@section('page-title', 'Category Details')

@section('content')
    <div class="glass-card p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h2 class="h4">{{ $category->name }}</h2>
                <div class="mt-2"><x-admin.status-badge :value="$category->status" /></div>
            </div>
            <a class="btn btn-outline-primary" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
        </div>
        <div class="row g-4 mt-1">
            <div class="col-lg-4">
                @if($category->image)
                    <img src="{{ asset('storage/' . $category->image) }}" class="img-fluid rounded-5" alt="{{ $category->name }}">
                @endif
            </div>
            <div class="col-lg-8">
                <div class="text-secondary">Description</div>
                <p class="mb-0">{{ $category->description ?: 'No description added.' }}</p>
            </div>
        </div>
    </div>
@endsection

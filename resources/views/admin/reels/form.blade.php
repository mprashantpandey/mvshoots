@extends('admin.layouts.app')

@section('page-title', $reel->exists ? 'Edit Reel' : 'Create Reel')

@section('content')
    <div class="glass-card p-4">
        <form method="POST" enctype="multipart/form-data" action="{{ $reel->exists ? route('admin.reels.update', $reel) : route('admin.reels.store') }}">
            @csrf
            @if($reel->exists) @method('PUT') @endif
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input class="form-control" name="title" value="{{ old('title', $reel->title) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="active" @selected(old('status', $reel->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $reel->status) === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Video URL</label>
                    <input class="form-control" name="video_url" value="{{ old('video_url', $reel->video_url) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Video File</label>
                    <input class="form-control" type="file" name="video_file">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Thumbnail</label>
                    <input class="form-control" type="file" name="thumbnail">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id">
                        <option value="">None</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((int) old('category_id', $reel->category_id) === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Save Reel</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.reels.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection

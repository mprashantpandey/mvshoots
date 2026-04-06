@extends('admin.layouts.app')

@section('page-title', $owner->exists ? 'Edit Owner' : 'Create Owner')

@section('content')
    <div class="glass-card p-4">
        <form method="POST" action="{{ $owner->exists ? route('admin.owners.update', $owner) : route('admin.owners.store') }}">
            @csrf
            @if($owner->exists) @method('PUT') @endif
            <div class="row g-4">
                <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $owner->name) }}" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" type="email" value="{{ old('email', $owner->email) }}" required></div>
                <div class="col-md-6"><label class="form-label">{{ $owner->exists ? 'New Password' : 'Password' }}</label><input class="form-control" name="password" type="password" {{ $owner->exists ? '' : 'required' }}></div>
                <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active" @selected(old('status', $owner->status) === 'active')>Active</option><option value="inactive" @selected(old('status', $owner->status) === 'inactive')>Inactive</option></select></div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Save Owner</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.owners.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@extends('admin.layouts.app')

@section('page-title', $partner->exists ? 'Edit Partner' : 'Create Partner')

@section('content')
    <div class="glass-card p-4">
        <form method="POST" action="{{ $partner->exists ? route('admin.partners.update', $partner) : route('admin.partners.store') }}">
            @csrf
            @if($partner->exists) @method('PUT') @endif
            <div class="row g-4">
                <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $partner->name) }}" required></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone', $partner->phone) }}" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $partner->email) }}"></div>
                <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active" @selected(old('status', $partner->status) === 'active')>Active</option><option value="inactive" @selected(old('status', $partner->status) === 'inactive')>Inactive</option></select></div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Save Partner</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.partners.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection

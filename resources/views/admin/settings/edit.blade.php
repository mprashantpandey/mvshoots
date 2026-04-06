@extends('admin.layouts.app')

@section('page-title', 'Settings')

@section('content')
    <div class="glass-card p-4">
        <h2 class="h5 mb-4">Platform Settings</h2>
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">App Name</label>
                    <input class="form-control" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Brand Logo</label>
                    <input class="form-control" type="file" name="branding_logo">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Email</label>
                    <input class="form-control" type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Phone</label>
                    <input class="form-control" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
@endsection

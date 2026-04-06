@extends('admin.layouts.app')

@section('page-title', 'Owner Details')

@section('content')
    <div class="glass-card p-4">
        <div class="row g-4">
            <div class="col-md-4"><strong>Name</strong><div>{{ $owner->name }}</div></div>
            <div class="col-md-4"><strong>Email</strong><div>{{ $owner->email }}</div></div>
            <div class="col-md-4"><strong>Status</strong><div><x-admin.status-badge :value="$owner->status" /></div></div>
        </div>
    </div>
@endsection

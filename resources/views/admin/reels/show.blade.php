@extends('admin.layouts.app')

@section('page-title', 'Reel Details')

@section('content')
    <div class="glass-card p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h2 class="h4">{{ $reel->title }}</h2>
                <p class="text-secondary mb-0">{{ $reel->category?->name ?? 'Unassigned category' }}</p>
            </div>
            <x-admin.status-badge :value="$reel->status" />
        </div>
        <div class="row g-4 mt-1">
            <div class="col-lg-4">
                @if($reel->thumbnail)
                    <img src="{{ str($reel->thumbnail)->startsWith('http') ? $reel->thumbnail : asset('storage/' . $reel->thumbnail) }}" class="img-fluid rounded-5">
                @endif
            </div>
            <div class="col-lg-8">
                <div><strong>Video Source</strong></div>
                <a href="{{ str($reel->video_url)->startsWith('http') ? $reel->video_url : asset('storage/' . $reel->video_url) }}" target="_blank">{{ $reel->video_url }}</a>
            </div>
        </div>
    </div>
@endsection

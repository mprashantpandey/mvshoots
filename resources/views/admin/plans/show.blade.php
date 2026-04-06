@extends('admin.layouts.app')

@section('page-title', 'Plan Details')

@section('content')
    <div class="glass-card p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h2 class="h4">{{ $plan->title }}</h2>
                <p class="text-secondary mb-0">{{ $plan->category?->name }}</p>
            </div>
            <x-admin.status-badge :value="$plan->status" />
        </div>
        <div class="row g-4 mt-1">
            <div class="col-md-4"><strong>Price</strong><div>₹{{ number_format($plan->price, 2) }}</div></div>
            <div class="col-md-4"><strong>Duration</strong><div>{{ $plan->duration }}</div></div>
            <div class="col-md-4"><strong>Category</strong><div>{{ $plan->category?->name }}</div></div>
            <div class="col-12"><strong>Description</strong><div>{{ $plan->description ?: 'No description' }}</div></div>
            <div class="col-12"><strong>Inclusions</strong><div>{!! is_array($plan->inclusions) ? implode('<br>', array_map('e', $plan->inclusions)) : 'No inclusions' !!}</div></div>
        </div>
    </div>
@endsection

<div class="glass-card stat-card">
    <div class="d-flex justify-content-between align-items-start gap-3">
        <div>
            <div class="text-secondary small">{{ $label }}</div>
            <div class="display-6 fw-semibold mt-2">{{ $value }}</div>
            @isset($hint)
                <div class="small text-secondary mt-2">{{ $hint }}</div>
            @endisset
        </div>
        <div class="stat-icon">
            <i class="bi {{ $icon }}"></i>
        </div>
    </div>
</div>

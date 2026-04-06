@php
    $map = [
        'active' => 'success',
        'inactive' => 'secondary',
        'pending' => 'warning',
        'confirmed' => 'info',
        'assigned' => 'primary',
        'accepted' => 'primary',
        'in_progress' => 'warning',
        'completed' => 'success',
        'paid' => 'success',
        'failed' => 'danger',
    ];
    $badge = $map[$value] ?? 'secondary';
@endphp

<span class="badge text-bg-{{ $badge }} badge-soft">{{ str($value)->headline() }}</span>

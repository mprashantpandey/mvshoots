<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = ['user_type', 'user_id', 'title', 'body', 'type', 'reference_id', 'is_read'];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['user_type'] ?? null, fn ($builder, $userType) => $builder->where('user_type', $userType))
            ->when($filters['type'] ?? null, fn ($builder, $type) => $builder->where('type', $type))
            ->when(array_key_exists('is_read', $filters) && $filters['is_read'] !== null && $filters['is_read'] !== '', function ($builder) use ($filters): void {
                $builder->where('is_read', filter_var($filters['is_read'], FILTER_VALIDATE_BOOL));
            })
            ->when($filters['search'] ?? null, function ($builder, $search): void {
                $builder->where(function ($nested) use ($search): void {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            });
    }
}

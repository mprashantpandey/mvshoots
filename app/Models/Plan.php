<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'description',
        'price',
        'duration',
        'inclusions',
        'status',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'inclusions' => 'array',
        'price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class)->withTimestamps();
    }
}

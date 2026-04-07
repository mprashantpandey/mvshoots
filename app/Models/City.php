<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'name',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function primaryPartners(): HasMany
    {
        return $this->hasMany(Partner::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class)->withTimestamps();
    }

    public function servicingPartners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class)->withTimestamps();
    }
}

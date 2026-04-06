<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'description', 'image', 'status'];

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    public function reels(): HasMany
    {
        return $this->hasMany(Reel::class);
    }
}

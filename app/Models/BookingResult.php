<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingResult extends Model
{
    protected $fillable = ['booking_id', 'file_url', 'file_type', 'uploaded_by_partner_id', 'notes'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'uploaded_by_partner_id');
    }
}

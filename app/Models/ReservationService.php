<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReservationService extends Model
{
    use HasUuids;

    protected $fillable = ['reservation_id', 'extra_service_id', 'price_snapshot', 'quantity'];

    protected function casts(): array
    {
        return ['price_snapshot' => 'decimal:2'];
    }

    public function reservation()  { return $this->belongsTo(Reservation::class); }
    public function extraService() { return $this->belongsTo(ExtraService::class); }

    public function getSubtotalAttribute(): float
    {
        return $this->price_snapshot * $this->quantity;
    }
}

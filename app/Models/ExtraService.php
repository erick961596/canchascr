<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ExtraService extends Model
{
    use HasUuids;

    protected $fillable = ['venue_id', 'name', 'description', 'price', 'active'];

    protected function casts(): array
    {
        return [
            'price'  => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function reservationServices()
    {
        return $this->hasMany(ReservationService::class);
    }

    public function scopeActive($q) { return $q->where('active', true); }
}

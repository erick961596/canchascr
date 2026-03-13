<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Promotion extends Model
{
    use HasUuids;

    protected $fillable = [
        'venue_id', 'name', 'description', 'type', 'value',
        'starts_at', 'ends_at', 'court_ids', 'active',
    ];

    protected function casts(): array
    {
        return [
            'value'      => 'decimal:2',
            'starts_at'  => 'date',
            'ends_at'    => 'date',
            'court_ids'  => 'array',
            'active'     => 'boolean',
        ];
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function isActiveNow(): bool
    {
        if (!$this->active) return false;
        $today = Carbon::today();
        return $today->between($this->starts_at, $this->ends_at);
    }

    /**
     * Aplica el descuento sobre un precio base y devuelve el monto a descontar.
     */
    public function calculateDiscount(float $basePrice): float
    {
        if ($this->type === 'percentage') {
            return round($basePrice * ($this->value / 100), 2);
        }
        return min((float) $this->value, $basePrice);
    }

    public function getDisplayLabelAttribute(): string
    {
        if ($this->type === 'percentage') {
            return number_format($this->value, 0) . '% de descuento';
        }
        return '₡' . number_format($this->value, 0, ',', '.') . ' de descuento';
    }

    public function scopeActive($q) { return $q->where('active', true); }

    public function scopeCurrentlyValid($q)
    {
        $today = now()->toDateString();
        return $q->where('active', true)
                 ->where('starts_at', '<=', $today)
                 ->where('ends_at',   '>=', $today);
    }

    public function appliesToCourt(string $courtId): bool
    {
        if (empty($this->court_ids)) return true; // aplica a todas
        return in_array($courtId, $this->court_ids);
    }
}

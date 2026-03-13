<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasUuids;

    protected $fillable = [
        'court_id','user_id','reservation_date','start_time','end_time',
        'total_price','status','payment_proof','payment_status','notes','sinpe_reference',
        'client_name','client_phone','is_manual','promotion_id','discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'total_price'      => 'decimal:2',
            'discount_amount'  => 'decimal:2',
            'is_manual'        => 'boolean',
        ];
    }

    public function court()     { return $this->belongsTo(Court::class); }
    public function user()      { return $this->belongsTo(User::class); }
    public function promotion() { return $this->belongsTo(\App\Models\Promotion::class); }
    public function services()  { return $this->hasMany(\App\Models\ReservationService::class); }

    public function getProofUrlAttribute(): ?string
    {
        if (!$this->payment_proof) return null;
        return \Storage::disk('s3')->url($this->payment_proof);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'confirmed'  => '<span class="badge badge-success">Confirmada</span>',
            'pending'    => '<span class="badge badge-warning">Pendiente</span>',
            'cancelled'  => '<span class="badge badge-danger">Cancelada</span>',
            'no_show'    => '<span class="badge badge-secondary">No asistió</span>',
            default      => '<span class="badge badge-secondary">' . $this->status . '</span>',
        };
    }

    public function scopePending($q)   { return $q->where('status', 'pending'); }
    public function scopeConfirmed($q) { return $q->where('status', 'confirmed'); }
    public function scopeUpcoming($q)  { return $q->where('reservation_date', '>=', now()->toDateString()); }
}

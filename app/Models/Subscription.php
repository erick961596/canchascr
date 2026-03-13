<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id','plan_id','status','payment_method','price',
        'payment_proof','onvo_subscription_id','onvo_payment_intent_id',
        'onvo_payment_method_id','onvo_period_end','starts_at','ends_at',
        'last_payment_at','failed_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'       => 'datetime',
            'ends_at'         => 'datetime',
            'last_payment_at' => 'datetime',
            'failed_at'       => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at?->isFuture();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active'    => '<span class="badge badge-success">Activa</span>',
            'pending'   => '<span class="badge badge-warning">Pendiente</span>',
            'past_due'  => '<span class="badge badge-danger">Vencida</span>',
            'failed'    => '<span class="badge badge-danger">Fallida</span>',
            'canceled'  => '<span class="badge badge-secondary">Cancelada</span>',
            default     => '<span class="badge badge-secondary">' . $this->status . '</span>',
        };
    }
}

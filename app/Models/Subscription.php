<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'payment_method',
        'price',
        'payment_proof',
        'onvo_subscription_id',
        'onvo_payment_intent_id',
        'onvo_payment_method_id',
        'onvo_customer_id',
        'starts_at',
        'ends_at',
        'last_payment_at',
    ];

    protected $casts = [
        'starts_at'       => 'datetime',
        'ends_at'         => 'datetime',
        'last_payment_at' => 'datetime',
    ];

    // -----------------------------------------------------------------------
    // Relaciones
    // -----------------------------------------------------------------------
    public function user()    { return $this->belongsTo(User::class); }
    public function plan()    { return $this->belongsTo(Plan::class); }
    public function payments(){ return $this->hasMany(SubscriptionPayment::class); }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at?->isFuture();
    }

    // -----------------------------------------------------------------------
    // Accessor: badge HTML para mostrar el estado
    // -----------------------------------------------------------------------
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active'     => '<span class="badge" style="background:#e8f5e9;color:#2e7d32">Activa</span>',
            'pending'    => '<span class="badge" style="background:#fff3e0;color:#e65100">Pendiente</span>',
            'incomplete' => '<span class="badge" style="background:#fff3e0;color:#e65100">Procesando</span>',
            'past_due'   => '<span class="badge" style="background:#fff3e0;color:#e65100">Vencida</span>',
            'failed'     => '<span class="badge" style="background:#ffebee;color:#c62828">Fallida</span>',
            'canceled'   => '<span class="badge" style="background:#f5f5f5;color:#888">Cancelada</span>',
            default      => '<span class="badge" style="background:#f5f5f5;color:#888">' . $this->status . '</span>',
        };
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('ends_at', '>', now());
    }
}

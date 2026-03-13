<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'name','email','password','role','google_id','avatar',
        'phone','onvo_customer_id','email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // Role helpers
    public function isAdmin(): bool  { return $this->role === 'admin'; }
    public function isOwner(): bool  { return $this->role === 'owner'; }
    public function isPlayer(): bool { return $this->role === 'user'; }

    // Última suscripción (para middleware y checks rápidos)
    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    // Historial completo de suscripciones
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function venues()
    {
        return $this->hasMany(Venue::class, 'owner_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription?->status === 'active'
            && $this->subscription?->ends_at?->isFuture();
    }

    public function canAddCourt(): bool
    {
        if (!$this->hasActiveSubscription()) return false;
        $limit   = $this->subscription->plan->court_limit ?? 0;
        $current = $this->venues()->withCount('activeCourts')->get()
            ->sum('active_courts_count');
        return $current < $limit;
    }
}

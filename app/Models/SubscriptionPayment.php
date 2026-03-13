<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    use HasUuids;

    protected $fillable = ['subscription_id','amount','method','status','proof_path','notes','paid_at'];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}

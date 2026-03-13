<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasUuids;

    protected $fillable = ['name','description','price','court_limit','onvopay_id','onvopay_price_id','active'];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function getPriceFormattedAttribute(): string
    {
        return '₡' . number_format($this->price, 0, ',', '.');
    }
}

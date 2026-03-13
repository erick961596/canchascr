<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Venue extends Model
{
    use HasUuids;

    protected $fillable = [
        'owner_id','name','slug','description','phone','address',
        'province','canton','district','lat','lng','logo','images',
        'amenities','active',
    ];

    protected function casts(): array
    {
        return [
            'images'    => 'array',
            'amenities' => 'array',
            'active'    => 'boolean',
            'lat'       => 'float',
            'lng'       => 'float',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($venue) {
            if (empty($venue->slug)) {
                $venue->slug = Str::slug($venue->name) . '-' . Str::random(6);
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function courts()
    {
        return $this->hasMany(Court::class);
    }

    public function activeCourts()
    {
        return $this->hasMany(Court::class)->where('active', true);
    }

    public function getLogoUrlAttribute(): string
    {
        if (!$this->logo) return asset('assets/img/venue-placeholder.png');
        return \Storage::disk('s3')->url($this->logo);
    }

    public function getImagesUrlsAttribute(): array
    {
        if (!$this->images) return [];
        return array_map(fn($p) => \Storage::disk('s3')->url($p), $this->images);
    }

    public function scopeActive($q)           { return $q->where('active', true); }
    public function scopeByProvince($q, $p)   { return $q->where('province', $p); }
    public function scopeByCanton($q, $c)     { return $q->where('canton', $c); }
    public function scopeByDistrict($q, $d)   { return $q->where('district', $d); }

    public function extraServices()
    {
        return $this->hasMany(\App\Models\ExtraService::class);
    }

    public function promotions()
    {
        return $this->hasMany(\App\Models\Promotion::class);
    }

    public function ratings()
    {
        return $this->hasMany(VenueRating::class);
    }

    public function getAvgRatingAttribute(): float
    {
        return round($this->ratings()->avg('rating') ?? 0, 1);
    }

    public function getRatingsCountAttribute(): int
    {
        return $this->ratings()->count();
    }
}
    // Ratings

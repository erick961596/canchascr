<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    use HasUuids;

    protected $fillable = [
        'venue_id','name','sport','price_per_hour','slot_duration',
        'features','images','active',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'images'   => 'array',
            'active'   => 'boolean',
            'price_per_hour' => 'decimal:2',
        ];
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function blockouts()
    {
        return $this->hasMany(Blockout::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function getImagesUrlsAttribute(): array
    {
        if (!$this->images) return [];
        return array_values(
            array_map(
                fn($p) => \Storage::disk('s3')->url($p),
                array_filter($this->images, fn($p) => !empty($p))
            )
        );
    }

    public function scopeActive($q) { return $q->where('active', true); }
    public function scopeBySport($q, $s) { return $q->where('sport', $s); }

    public static function sportLabel(string $sport): string
    {
        return match($sport) {
            'futbol'      => 'Fútbol',
            'basquetbol'  => 'Baloncesto',
            'tenis'       => 'Tenis',
            'padel'       => 'Pádel',
            'volleyball'  => 'Volleyball',
            'beisbol'     => 'Béisbol',
            default       => 'Otro',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VenueRating extends Model
{
    use HasUuids;

    protected $fillable = ['venue_id', 'user_id', 'rating', 'comment'];

    public function venue() { return $this->belongsTo(Venue::class); }
    public function user()  { return $this->belongsTo(User::class); }
}

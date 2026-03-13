<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LocationData extends Model {
    public $timestamps = false;
    protected $table = 'location_data';
    protected $fillable = ['province','canton','district'];
}

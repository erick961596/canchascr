<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasUuids;

    protected $fillable = ['court_id','day_of_week','open_time','close_time','active'];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}

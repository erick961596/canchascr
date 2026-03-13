<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Blockout extends Model
{
    use HasUuids;

    protected $fillable = ['court_id','block_date','start_time','end_time','full_day','reason'];

    protected function casts(): array
    {
        return [
            'block_date' => 'date',
            'full_day'   => 'boolean',
        ];
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}

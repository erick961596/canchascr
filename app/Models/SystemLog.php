<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasUuids;

    protected $fillable = ['level','type','user_id','subject','context','ip','user_agent'];
    protected $casts    = ['context' => 'array'];

    public function user() { return $this->belongsTo(User::class); }

    // Level badge colors
    public function getLevelBadgeAttribute(): string
    {
        return match($this->level) {
            'error'        => 'background:#ffebee;color:#c62828',
            'warning'      => 'background:#fff3e0;color:#e65100',
            'payment'      => 'background:#e8f5e9;color:#2e7d32',
            'subscription' => 'background:#e8eaf6;color:#283593',
            'auth'         => 'background:#f3e5f5;color:#6a1b9a',
            default        => 'background:#f5f5f5;color:#555',
        };
    }
}

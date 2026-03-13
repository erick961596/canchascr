<?php namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model {
    use HasUuids;
    protected $table = 'notifications';
    protected $fillable = ['user_id','channel','type','payload','read','sent_at'];
    protected $casts = ['payload' => 'array', 'read' => 'boolean', 'sent_at' => 'datetime'];
    public function user() { return $this->belongsTo(User::class); }
}

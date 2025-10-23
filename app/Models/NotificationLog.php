<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
     protected $fillable = [
        'violation_id',
        'user_id',
        'channel',
        'status',
        'message',
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }
}

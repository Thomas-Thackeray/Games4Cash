<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = ['session_id', 'ip_hash', 'path', 'referrer'];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}

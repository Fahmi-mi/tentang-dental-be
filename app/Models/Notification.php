<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $timestamps = false;

    protected $table = 'notification';

    protected $fillable = [
        'admin_id',
        'title',
        'message',
        'type',
        'is_read',
        'created_at',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
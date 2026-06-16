<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasFactory;

    protected $table = 'notification';

    protected $fillable = [
        'admin_id',
        'title',
        'message',
        'type',
        'is_read',
    ];

    public const UPDATED_AT = null;

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'detail',
        'icon',
        'article_content',
        'support_image',
    ];

    protected $appends = ['icon_url', 'support_image_url'];

    // Accessor for icon URL
    public function getIconUrlAttribute()
    {
        if ($this->icon) {
            return asset('storage/' . $this->icon);
        }
        return null;
    }

    // Accessor for support_image URL
    public function getSupportImageUrlAttribute()
    {
        if ($this->support_image) {
            return asset('storage/' . $this->support_image);
        }
        return null;
    }

    // Relationship
    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'reservation_service')
                    ->withTimestamps();
    }
}

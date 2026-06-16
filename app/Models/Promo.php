<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'detail',
        'original_price',
        'promo_price',
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'promo_price' => 'decimal:2',
    ];

    protected $appends = ['image_url'];

    // Accessor for image URL
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rating',
        'testimoni',
        'photo',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    protected $appends = ['photo_url'];

    // Accessor for photo URL
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'specialization',
        'photo',
        'schedule',
        'statement',
    ];

    protected $casts = [
        'schedule' => 'array',
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

    // Relationship
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}

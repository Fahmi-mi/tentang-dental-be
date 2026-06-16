<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nickname',
        'gender',
        'age',
        'birth_place',
        'birth_date',
        'address',
        'village',
        'district',
        'city',
        'phone',
        'occupation',
        'parent_name',
        'height',
        'weight',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'age' => 'integer',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    // Relationships
    public function medicalHistory()
    {
        return $this->hasOne(PatientMedicalHistory::class);
    }

    public function dentalHistory()
    {
        return $this->hasOne(PatientDentalHistory::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function latestReservation()
    {
        return $this->hasOne(Reservation::class)->latestOfMany('reservation_date');
    }

    public function rontgens()
    {
        return $this->hasMany(Rontgen::class);
    }
}

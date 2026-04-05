<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'patient_category',
        'doctor_id',
        'complain',
        'reservation_date',
        'birth_date',
        'age',
        'appointment_time',
        'status',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'birth_date' => 'date',
        'age' => 'integer',
        'appointment_time' => 'string',
    ];

    public $timestamps = false;

    protected $dates = ['created_at'];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'reservation_service')
                    ->withTimestamps();
    }
}

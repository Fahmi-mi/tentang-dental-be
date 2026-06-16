<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDentalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'frequent_tooth_pain',
        'tooth_pain_detail',
        'bleeding_gums',
        'ever_dental_treatment',
        'dental_treatment_detail',
        'brushing_frequency',
        'use_floss_or_mouthwash',
        'bad_habits',
        'bad_habits_detail',
        'ever_braces',
        'braces_years',
        'root_canal_treatment',
        'root_canal_detail',
        'dentures',
        'routine_checkup',
        'dental_checkup_frequency',
        'doctor_notes',
    ];

    protected $casts = [
        'frequent_tooth_pain' => 'boolean',
        'bleeding_gums' => 'boolean',
        'ever_dental_treatment' => 'boolean',
        'use_floss_or_mouthwash' => 'boolean',
        'bad_habits' => 'boolean',
        'ever_braces' => 'boolean',
        'braces_years' => 'integer',
        'root_canal_treatment' => 'boolean',
        'dentures' => 'boolean',
        'routine_checkup' => 'boolean',
    ];

    public $timestamps = false;

    protected $dates = ['created_at'];

    // Relationship
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}

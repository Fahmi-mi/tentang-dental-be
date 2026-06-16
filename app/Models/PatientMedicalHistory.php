<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientMedicalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'has_allergy',
        'allergy_detail',
        'has_systemic_disease',
        'systemic_disease_detail',
        'undergoing_treatment',
        'treatment_detail',
        'ever_hospitalized',
        'hospitalized_reason',
        'smoking_or_alcohol',
    ];

    protected $casts = [
        'has_allergy' => 'boolean',
        'has_systemic_disease' => 'boolean',
        'undergoing_treatment' => 'boolean',
        'ever_hospitalized' => 'boolean',
        'smoking_or_alcohol' => 'boolean',
    ];

    public $timestamps = false;

    protected $dates = ['created_at'];

    // Relationship
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}

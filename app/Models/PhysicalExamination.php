<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicalExamination extends Model
{
    use HasFactory;

    protected $fillable = [
        'rontgen_id',
        'blood_pressure',
        'height',
        'weight',
        'pulse',
        'respiration',
        'temperature',
    ];

    public $timestamps = false;

    protected $casts = [
        'temperature' => 'decimal:1',
        'created_at'  => 'datetime',
    ];

    public function rontgen()
    {
        return $this->belongsTo(Rontgen::class);
    }
}
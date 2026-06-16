<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentalExamination extends Model
{
    use HasFactory;

    protected $fillable = [
        'rontgen_id',
        'visit_number',
        'visit_date',
        'subjective',
        'objective',
        'assessment',
        'planning',
        'treatment',
    ];

    public $timestamps = false;

    protected $casts = [
        'visit_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function rontgen()
    {
        return $this->belongsTo(Rontgen::class);
    }

    public function examinationImages()
    {
        return $this->hasMany(ExaminationImage::class);
    }

    public function fotoBefore()
    {
        return $this->hasOne(ExaminationImage::class)->where('image_phase', 'before');
    }

    public function fotoAfter()
    {
        return $this->hasOne(ExaminationImage::class)->where('image_phase', 'after');
    }
}
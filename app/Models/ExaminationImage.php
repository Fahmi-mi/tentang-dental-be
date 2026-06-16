<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExaminationImage extends Model
{
    use HasFactory;

    protected $table = 'examination_images';

    protected $fillable = [
        'rontgen_id',
        'dental_examination_id', 
        'image_path',
        'image_type',
        'image_phase',
    ];

    public const UPDATED_AT = null;

    public function rontgen()
    {
        return $this->belongsTo(Rontgen::class);
    }

    public function dentalExamination()
    {
        return $this->belongsTo(DentalExamination::class);
    }
}
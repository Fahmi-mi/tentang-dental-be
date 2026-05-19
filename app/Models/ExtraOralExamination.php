<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraOralExamination extends Model
{
    use HasFactory;

    protected $fillable = [
        'rontgen_id',
        'face',
        'facial_skin_neck',
        'lymph_nodes',
        'temporomandibular_joint',
        'muscle_mass',
        'facial_swelling',
        'eyes_nose',
    ];

    public function rontgen()
    {
        return $this->belongsTo(Rontgen::class);
    }
}
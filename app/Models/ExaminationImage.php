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
        'image_path',
        'image_type',
    ];

    public const UPDATED_AT = null;

    public function rontgen()
    {
        return $this->belongsTo(Rontgen::class);
    }
}

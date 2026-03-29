<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamminationTag extends Model
{
    use HasFactory;

    protected $table = 'exammination_tags';

    public $timestamps = false;

    protected $fillable = [
        'rontgen_id',
        'tag_id',
    ];

    public function rontgen()
    {
        return $this->belongsTo(Rontgen::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}

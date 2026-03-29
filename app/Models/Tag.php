<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';

    protected $fillable = [
        'tag_name',
        'detail',
    ];

    public const UPDATED_AT = null;

    public function rontgens()
    {
        return $this->belongsToMany(Rontgen::class, 'exammination_tags', 'tag_id', 'rontgen_id');
    }
}

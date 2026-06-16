<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Rontgen extends Model
{
    use HasFactory;

    protected $table = 'rontgen';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'detail',
        'status',
        'chief_complaint',
        'target_foto',
    ];

    protected $appends = ['latest_image_url'];

    public function getLatestImageUrlAttribute(): ?string
    {
        $imagePath = optional($this->primaryImage)->image_path;

        if (!$imagePath) {
            return null;
        }

        if (Storage::disk('public')->exists('rontgen/' . $imagePath)) {
            return asset('storage/rontgen/' . $imagePath);
        }

        if (Storage::disk('public')->exists('rontgens/' . $imagePath)) {
            return asset('storage/rontgens/' . $imagePath);
        }

        return null;
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function examinationImages()
    {
        return $this->hasMany(ExaminationImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(ExaminationImage::class)->latestOfMany('id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'exammination_tags', 'rontgen_id', 'tag_id');
    }

    public function physical_examination()
    {
        return $this->hasOne(PhysicalExamination::class, 'rontgen_id');
    }

    public function extra_oral_examination()
    {
        return $this->hasOne(ExtraOralExamination::class, 'rontgen_id');
    }
}

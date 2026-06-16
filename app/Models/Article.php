<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'title',
        'slug',
        'content',
        'image',
    ];

    protected $appends = ['image_url'];

    // Accessor for image URL
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return null;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
                
                $count = 1;
                while (static::where('slug', $article->slug)->exists()) {
                    $article->slug = Str::slug($article->title) . '-' . $count;
                    $count++;
                }
            }
        });

        static::updating(function ($article) {
            if ($article->isDirty('title') && empty($article->slug)) {
                $article->slug = Str::slug($article->title);
                
                $count = 1;
                while (static::where('slug', $article->slug)->where('id', '!=', $article->id)->exists()) {
                    $article->slug = Str::slug($article->title) . '-' . $count;
                    $count++;
                }
            }
        });
    }

    // Relationship
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}

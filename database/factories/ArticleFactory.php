<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $title = fake()->sentence(6);
        
        return [
            'admin_id' => Admin::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => fake()->paragraphs(10, true),
            'image' => null,
        ];
    }
}

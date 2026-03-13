<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $services = ['Scaling', 'Bleaching', 'Cabut Gigi', 'Tambal Gigi', 'Pasang Behel', 'Veneer', 'Implant'];
        
        return [
            'name' => fake()->randomElement($services),
            'detail' => fake()->sentence(15),
            'icon' => null,
            'article_content' => fake()->paragraphs(5, true),
            'support_image' => null,
        ];
    }
}

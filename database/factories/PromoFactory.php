<?php

namespace Database\Factories;

use App\Models\Promo;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromoFactory extends Factory
{
    protected $model = Promo::class;

    public function definition(): array
    {
        $originalPrice = fake()->numberBetween(100000, 500000);
        $promoPrice = $originalPrice * 0.7;
        
        return [
            'name' => 'Promo ' . fake()->word(),
            'image' => null,
            'detail' => fake()->sentence(20),
            'original_price' => $originalPrice,
            'promo_price' => $promoPrice,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\AddonPricingUnit;
use App\Models\Addon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Addon>
 */
class AddonFactory extends Factory
{
    protected $model = Addon::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'label' => ucfirst($name),
            'price_pence' => fake()->numberBetween(2000, 8000),
            'price_max_pence' => fake()->numberBetween(9000, 12000),
            'pricing_unit' => AddonPricingUnit::Flat,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}

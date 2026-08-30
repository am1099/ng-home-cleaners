<?php

namespace Database\Factories;

use App\Enums\ServiceIcon;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' clean';

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'card_title' => ucfirst($name),
            'short_description' => fake()->sentence(),
            'estimate_description' => fake()->sentence(),
            'full_description' => fake()->paragraph(),
            'icon' => fake()->randomElement(ServiceIcon::cases()),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\RecentWork;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecentWork>
 */
class RecentWorkFactory extends Factory
{
    protected $model = RecentWork::class;

    public function definition(): array
    {
        return [
            'before_image_path' => 'recent-work/before-placeholder.jpg',
            'after_image_path' => 'recent-work/after-placeholder.jpg',
            'title' => fake()->words(3, true),
            'description' => strtoupper(fake()->words(4, true)),
            'alt_text_before' => 'Before cleaning',
            'alt_text_after' => 'After cleaning',
            'sort_order' => 0,
            'is_published' => true,
            'published_at' => now(),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}

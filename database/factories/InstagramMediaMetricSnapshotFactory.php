<?php

namespace Database\Factories;

use App\Models\InstagramMediaMetricSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InstagramMediaMetricSnapshot>
 */
class InstagramMediaMetricSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'instagram_media_id' => \App\Models\InstagramMedia::factory(),
            'captured_at' => now(),
            'likes' => $this->faker->numberBetween(0, 1000),
            'comments_count' => $this->faker->numberBetween(0, 500),
            'shares' => $this->faker->numberBetween(0, 200),
            'saves' => $this->faker->numberBetween(0, 300),
            'reach' => $this->faker->numberBetween(1000, 5000),
            'views' => $this->faker->numberBetween(1200, 6000),
            'raw_payload' => ['dummy' => 'payload', 'type' => 'metric'],
        ];
    }
}

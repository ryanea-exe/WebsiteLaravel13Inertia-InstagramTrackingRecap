<?php

namespace Database\Factories;

use App\Models\InstagramMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InstagramMedia>
 */
class InstagramMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'instagram_account_id' => \App\Models\InstagramAccount::factory(),
            'external_media_id' => 'dummy_media_' . $this->faker->unique()->numerify('##########'),
            'media_type' => $this->faker->randomElement(['IMAGE', 'VIDEO', 'CAROUSEL_ALBUM']),
            'product_type' => 'FEED',
            'caption' => $this->faker->sentence(),
            'permalink' => 'https://example.test/media/' . $this->faker->uuid(),
            'media_url' => $this->faker->imageUrl(),
            'thumbnail_url' => $this->faker->imageUrl(),
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'raw_payload' => ['dummy' => 'payload', 'type' => 'media'],
        ];
    }
}

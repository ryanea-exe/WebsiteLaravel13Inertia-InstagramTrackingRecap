<?php

namespace Database\Factories;

use App\Models\InstagramAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InstagramAccount>
 */
class InstagramAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'facebook_page_id' => $this->faker->boolean(50) ? 'dummy_fb_' . $this->faker->unique()->numerify('##########') : null,
            'instagram_user_id' => 'dummy_ig_source_' . $this->faker->unique()->numerify('##########'),
            'username' => 'source_account_' . $this->faker->unique()->word(),
            'name' => $this->faker->company(),
            'account_type' => 'BUSINESS',
            'connection_status' => 'CONNECTED',
            'access_token' => 'dummy-access-token-' . $this->faker->uuid(),
            'token_expires_at' => now()->addDays(60),
            'last_synced_at' => now(),
        ];
    }

    public function connected(): self
    {
        return $this->state(fn (array $attributes) => [
            'connection_status' => 'CONNECTED',
            'access_token' => 'dummy-access-token-' . $this->faker->uuid(),
        ]);
    }

    public function disconnected(): self
    {
        return $this->state(fn (array $attributes) => [
            'connection_status' => 'DISCONNECTED',
            'access_token' => null,
            'token_expires_at' => null,
        ]);
    }

    public function expiredToken(): self
    {
        return $this->state(fn (array $attributes) => [
            'connection_status' => 'CONNECTED',
            'token_expires_at' => now()->subDays(1),
        ]);
    }
}

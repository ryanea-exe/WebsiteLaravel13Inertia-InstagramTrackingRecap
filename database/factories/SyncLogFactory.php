<?php

namespace Database\Factories;

use App\Models\SyncLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SyncLog>
 */
class SyncLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 week', 'now');
        $end = (clone $start)->modify('+5 minutes');
        return [
            'instagram_account_id' => \App\Models\InstagramAccount::factory(),
            'sync_type' => 'FULL',
            'status' => 'COMPLETED',
            'started_at' => $start,
            'finished_at' => $end,
            'records_processed' => $this->faker->numberBetween(10, 100),
            'error_message' => null,
            'metadata' => ['dummy' => 'sync_log'],
        ];
    }
}

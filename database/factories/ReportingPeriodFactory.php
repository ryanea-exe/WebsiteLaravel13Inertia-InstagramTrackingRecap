<?php

namespace Database\Factories;

use App\Models\ReportingPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportingPeriod>
 */
class ReportingPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-2 months', '-1 month');
        $end = (clone $start)->modify('+1 month');
        return [
            'name' => 'Period ' . $start->format('M Y'),
            'start_at' => $start,
            'end_at' => $end,
            'timezone' => 'Asia/Jakarta',
            'status' => 'ACTIVE',
        ];
    }
}

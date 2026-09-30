<?php

namespace Database\Factories;

use App\Models\InstagramComment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InstagramComment>
 */
class InstagramCommentFactory extends Factory
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
            'external_comment_id' => 'dummy_comment_' . $this->faker->unique()->numerify('##########'),
            'commenter_instagram_user_id' => 'dummy_ig_user_' . $this->faker->numerify('#####'),
            'commenter_username' => $this->faker->userName(),
            'matched_employee_id' => null,
            'text' => $this->faker->sentence(),
            'commented_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'raw_payload' => ['dummy' => 'payload', 'type' => 'comment'],
        ];
    }

    public function matched(): self
    {
        return $this->state(fn (array $attributes) => [
            'matched_employee_id' => \App\Models\Employee::factory(),
        ]);
    }

    public function unmatched(): self
    {
        return $this->state(fn (array $attributes) => [
            'matched_employee_id' => null,
        ]);
    }

    public function deleted(): self
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }

    public function fromEmployee(\App\Models\Employee $employee): self
    {
        return $this->state(fn (array $attributes) => [
            'commenter_instagram_user_id' => $employee->instagram_user_id,
            'commenter_username' => $employee->instagram_username,
            'matched_employee_id' => $employee->id,
        ]);
    }
}

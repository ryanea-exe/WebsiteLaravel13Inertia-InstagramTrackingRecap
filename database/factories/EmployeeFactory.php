<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_code' => 'EMP-' . $this->faker->unique()->numerify('#####'),
            'name' => $this->faker->name(),
            'department' => $this->faker->randomElement(['IT', 'Marketing', 'HR', 'Finance']),
            'instagram_user_id' => null,
            'instagram_username' => null,
            'instagram_link_status' => 'UNLINKED',
            'instagram_linked_at' => null,
            'is_active' => true,
        ];
    }

    public function linkedToInstagram(): self
    {
        return $this->state(fn (array $attributes) => [
            'instagram_user_id' => 'dummy_ig_' . $this->faker->unique()->numerify('######'),
            'instagram_username' => 'employee_test_' . $this->faker->unique()->numerify('###'),
            'instagram_link_status' => 'LINKED',
            'instagram_linked_at' => now(),
        ]);
    }
}

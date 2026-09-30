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
            'instagram_user_id' => $this->faker->unique()->numerify('##########'),
            'instagram_username' => $this->faker->unique()->userName(),
            'is_active' => true,
        ];
    }
}

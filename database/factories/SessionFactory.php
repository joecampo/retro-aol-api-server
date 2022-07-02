<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Session>
 */
class SessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid,
            'identity_id' => Hash::make('127.0.0.1')
        ];
    }
}

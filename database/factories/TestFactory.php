<?php

namespace Database\Factories;

use App\Models\Test;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestFactory extends Factory
{
    protected $model = Test::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->name(),
            'user_id' => User::factory(),
            'duration' => $this->faker->randomNumber(3),
            'renewals_count' => $this->faker->randomNumber(1),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->state([
            'name' => 'Super Admin',
            'username' => 'super-admin',
            'password' => 'password'
        ])->create();

        User::factory()->count(20)->create();
    }
}

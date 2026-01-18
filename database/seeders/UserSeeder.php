<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database with users.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'thijs.demaa@volteuropa.org'],
            [
                'name' => 'Thijs de Maa',
                'password' => Hash::make('dSK39CBgs2qMifLu'),
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InternSeeder extends Seeder
{
    public function run(): void
    {
        $interns = [
            ['name' => 'PS',   'email' => 'ps@drs.com',   'intern_role' => 'senior_programmer'],
            ['name' => 'RK',   'email' => 'rk@drs.com',   'intern_role' => 'mid_programmer'],
            ['name' => 'Rose', 'email' => 'rose@drs.com', 'intern_role' => 'translator'],
        ];

        foreach ($interns as $intern) {
            User::updateOrCreate(
                ['email' => $intern['email']],
                [
                    'name' => $intern['name'],
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'intern_role' => $intern['intern_role'],
                ],
            );
        }
    }
}

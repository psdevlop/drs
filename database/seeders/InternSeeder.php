<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InternSeeder extends Seeder
{
    public function run(): void
    {
        $cohort = [
            ['name' => 'PS',   'email' => 'ps@drs.com',   'team_role' => 'team_manager', 'intern_role' => 'senior_programmer'],
            ['name' => 'RK',   'email' => 'rk@drs.com',   'team_role' => 'team_member',  'intern_role' => 'mid_programmer'],
            ['name' => 'Rose', 'email' => 'rose@drs.com', 'team_role' => 'team_member',  'intern_role' => 'translator'],
        ];

        foreach ($cohort as $person) {
            User::updateOrCreate(
                ['email' => $person['email']],
                [
                    'name' => $person['name'],
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'team_role' => $person['team_role'],
                    'intern_role' => $person['intern_role'],
                ],
            );
        }
    }
}

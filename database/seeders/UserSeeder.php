<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'System Admin',
                'email' => 'admin@taya.gov.ph',
                'role' => 'admin',
                'facility_id' => 1,
            ],
            [
                'name' => 'Authorized User - PAO Lawyer',
                'email' => 'pao@taya.gov.ph',
                'role' => 'authorized_user',
                'facility_id' => null,
            ],
            [
                'name' => 'Authorized User - NGO Lawyer',
                'email' => 'ngo@taya.gov.ph',
                'role' => 'authorized_user',
                'facility_id' => null,
            ],
            [
                'name' => 'Authorized User - Court Admin',
                'email' => 'court@taya.gov.ph',
                'role' => 'authorized_user',
                'facility_id' => null,
            ],
            [
                'name' => 'Authorized User - Policy Advocate',
                'email' => 'policy@taya.gov.ph',
                'role' => 'authorized_user',
                'facility_id' => null,
            ],
        ];

        foreach ($users as $userData) {
            User::create([
                ...$userData,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }
    }
}

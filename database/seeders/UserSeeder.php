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
                'name' => 'BJMP Staff Officer',
                'email' => 'bjmp@taya.gov.ph',
                'role' => 'bjmp_staff',
                'facility_id' => 1,
            ],
            [
                'name' => 'PAO Atty. Maria Santos',
                'email' => 'pao@taya.gov.ph',
                'role' => 'pao_lawyer',
                'facility_id' => null,
            ],
            [
                'name' => 'NGO Atty. Juan Cruz',
                'email' => 'ngo@taya.gov.ph',
                'role' => 'ngo_lawyer',
                'facility_id' => null,
            ],
            [
                'name' => 'Court Administrator',
                'email' => 'court@taya.gov.ph',
                'role' => 'court_admin',
                'facility_id' => null,
            ],
            [
                'name' => 'Policy Advocate',
                'email' => 'policy@taya.gov.ph',
                'role' => 'policy_advocate',
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

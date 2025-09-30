<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['Admin', 'Pharmacist', 'Preparator', 'Accountant'];
        foreach ($roles as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $role]);
        }

        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@pharma.com',
                'password' => bcrypt('password'),
                'role' => 'Admin',
            ],
            [
                'name' => 'Pharmacist 1',
                'email' => 'pharma1@pharma.com',
                'password' => bcrypt('password'),
                'role' => 'Pharmacist',
            ],
            [
                'name' => 'Pharmacist 2',
                'email' => 'pharma2@pharma.com',
                'password' => bcrypt('password'),
                'role' => 'Pharmacist',
            ],
            [
                'name' => 'Preparator',
                'email' => 'prep@pharma.com',
                'password' => bcrypt('password'),
                'role' => 'Preparator',
            ],
            [
                'name' => 'Accountant',
                'email' => 'account@pharma.com',
                'password' => bcrypt('password'),
                'role' => 'Accountant',
            ],
        ];

        foreach ($users as $userData) {
            $user = \App\Models\User::firstOrCreate([
                'email' => $userData['email'],
            ], [
                'name' => $userData['name'],
                'password' => $userData['password'],
            ]);
            $user->assignRole($userData['role']);
        }
    }
}

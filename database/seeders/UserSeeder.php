<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Admin Pritani', 'email' => 'admin@pritani.test', 'password' => 'admin123', 'role' => 'admin'],
            ['name' => 'Operator Giling', 'email' => 'operator@pritani.test', 'password' => 'operator123', 'role' => 'operator'],
            ['name' => 'Kasir Toko', 'email' => 'kasir@pritani.test', 'password' => 'kasir123', 'role' => 'cashier'],
        ];

        foreach ($accounts as $account) {
            $user = User::where('email', $account['email'])->first() ?? new User();

            $user->forceFill([
                'name' => $account['name'],
                'email' => $account['email'],
                'password' => Hash::make($account['password']),
                'role' => $account['role'],
                'email_verified_at' => now(),
            ])->save();
        }
    }
}
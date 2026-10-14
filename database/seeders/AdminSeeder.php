<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@neoshop.com'],
            [
                'name'     => 'Administrador',
                'phone' => '(11) 90000-0000',
                'role'     => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        // Vendor
        User::updateOrCreate(
            ['email' => 'vendor@neoshop.com'],
            [
                'name'     => 'Vendedor Teste',
                'phone' => '(11) 91111-1111',
                'role'     => 'vendor',
                'password' => Hash::make('password'),
            ]
        );

        // User comum
        User::updateOrCreate(
            ['email' => 'user@neoshop.com'],
            [
                'name'     => 'Cliente Teste',
                'phone' => '(11) 92222-2222',
                'role'     => 'user',
                'password' => Hash::make('password'),
            ]
        );
    }
}
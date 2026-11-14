<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@tickets.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
        ]);

        // Create regular user for testing
        User::create([
            'name' => 'Usuario Test',
            'email' => 'user@tickets.com',
            'password' => Hash::make('user123'),
            'is_admin' => false,
        ]);

        $this->command->info('Admin user created: admin@tickets.com / admin123');
        $this->command->info('Regular user created: user@tickets.com / user123');
    }
}

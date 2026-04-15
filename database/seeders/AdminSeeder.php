<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gameportal.com'],
            [
                'first_name'           => 'Admin',
                'surname'              => 'User',
                'name'                 => 'Admin User',
                'username'             => 'admin1234567',
                'email'                => 'admin@gameportal.com',
                'contact_number'       => '00000000000',
                'password'             => Hash::make('Admin@12345678'),
                'role'                 => 'admin',
                'force_password_reset' => false,
            ]
        );

        $this->command->info('Admin user created/updated.');
        $this->command->info('  Email:    admin@gameportal.com');
        $this->command->info('  Username: admin1234567');
        $this->command->info('  Password: Admin@12345678');
    }
}

<?php

namespace Database\Seeders;

use App\Models\BlacklistedPassword;
use Illuminate\Database\Seeder;

class BlacklistSeeder extends Seeder
{
    public function run(): void
    {
        $passwords = [
            'password12345', 'password123456', 'password1234567',
            'p@ssword12345', 'p@ssword123456', 'p@ssw0rd12345',
            'p@ssw0rd1234!', 'passw0rd12345', 'passw0rd123456',
            'qwerty123456', 'qwerty1234567', 'qwerty12345!',
            'qwerty12345678', 'qwertyuiop12',
            'asdfghjkl123', 'zxcvbnm12345',
            '1qaz2wsx3edc', '1qaz2wsx!',
            '123456789012', '1234567890123', '12345678901234',
            '123456789012!', '1234567890!2',
            'iloveyou12345', 'iloveyou123456', 'iloveyou1234!',
            'welcome12345!', 'welcome123456', 'welcome1234!',
            'letmein12345!', 'letmein123456', 'letmein1234!',
            'monkey123456!', 'dragon123456!', 'master123456!',
            'admin12345678', 'admin123456!', 'admin1234567!',
            'trustno112345', 'trustno11234!',
            'superman12345', 'superman1234!',
            'batman123456!', 'batman1234567',
            'football12345', 'football1234!',
            'baseball12345', 'baseball1234!',
            'sunshine12345', 'sunshine1234!',
            'princess12345', 'princess1234!',
            'shadow1234567', 'shadow123456!',
            'michael12345!', 'jessica12345!',
            'charlie12345!', 'thomas123456!',
            'jordan123456!', 'hunter123456!',
            'daniel123456!', 'andrew123456!',
            'george123456!', 'joshua123456!',
            'winter123456!', 'summer123456!',
            'spring123456!', 'autumn123456!',
            'harley123456!', 'dakota123456!',
            'chelsea12345!', 'ranger123456!',
            'hello123456789', 'hello12345678!',
            'abc123456789!', 'abcdefgh1234!',
            'aaaaaa123456!', 'aaaaaaaaaaaa1',
            '111111111112!', '000000000001!',
        ];

        foreach ($passwords as $pw) {
            BlacklistedPassword::firstOrCreate(['password' => strtolower($pw)]);
        }

        $this->command->info('Blacklisted ' . count($passwords) . ' passwords seeded.');
    }
}

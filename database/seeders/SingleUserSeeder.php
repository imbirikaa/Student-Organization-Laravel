<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SingleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Ali',
            'last_name' => 'Imbirika',
            'email' => 'ali@imbirika.com',
            'phone' => '0912345678',
            'password' => Hash::make('password123'),
            'birth_date' => '1998-05-25',
            'school' => 'Tripoli University',
            'department_id' => 1, // make sure department with ID 1 exists
            'graduate_date' => '2024-07-01',
            'nickname' => 'imbirika',
            'about' => 'Full-stack developer and tech enthusiast.',
            'profile_picture' => 'https://via.placeholder.com/200x200.png/00bb66?text=people+animi', // example path
            'membership_date' => now(),
            'email_verified' => true,
            'phone_verified' => true,
            'is_active' => true,
        ]);
    }
}

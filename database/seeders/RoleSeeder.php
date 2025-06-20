<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web',]);
        Role::create(['name' => 'editor', 'guard_name' => 'web',]);
        Role::create(['name' => 'user', 'guard_name' => 'web',]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommunityRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $roles = [
            ['id' => 1, 'role' => 'Kurucu', 'description' => 'Topluluğun kurucusu.', 'community_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'role' => 'Yönetici', 'description' => 'Topluluğun yöneticisi.', 'community_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'role' => 'Moderatör', 'description' => 'Topluluğun moderatörü.', 'community_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'role' => 'Üye', 'description' => 'Topluluğun standart üyesi.', 'community_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('community_roles')->insert($roles);
    }
}

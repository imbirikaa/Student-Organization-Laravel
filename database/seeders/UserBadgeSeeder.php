<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Badge;
use Illuminate\Support\Facades\DB;

class UserBadgeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $badgeIds = range(1, 5); // or Badge::pluck('id')->toArray()

        foreach ($users as $user) {
            $assignedBadges = collect($badgeIds)->random(rand(1, count($badgeIds)));

            foreach ($assignedBadges as $badgeId) {
                DB::table('user_badges')->updateOrInsert(
                    ['user_id' => $user->id, 'badge_id' => $badgeId],
                    ['assigned_date' => now()->subDays(rand(1, 1000))]
                );
            }
        }
    }
}

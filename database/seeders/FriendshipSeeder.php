<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Friendship;
use Illuminate\Support\Facades\DB;

class FriendshipSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id')->toArray();
        $statusOptions = ['pending', 'accepted', 'rejected'];
        $usedPairs = [];

        foreach ($users as $userId) {
            // Try to create 3 friendships per user
            for ($i = 0; $i < 3; $i++) {
                $friendId = $userId;
                while (
                    $friendId === $userId ||
                    in_array([$userId, $friendId], $usedPairs) ||
                    in_array([$friendId, $userId], $usedPairs)
                ) {
                    $friendId = $users[array_rand($users)];
                }

                $usedPairs[] = [$userId, $friendId];

                Friendship::create([
                    'user_id'        => $userId,
                    'friend_user_id' => $friendId,
                    'status'         => $statusOptions[array_rand($statusOptions)],
                    'request_date'   => now()->subDays(rand(1, 30)),
                    'response_date'  => now()->subDays(rand(0, 30)),
                ]);
            }
        }
    }
}

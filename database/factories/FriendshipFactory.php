<?php

use App\Models\User;
use App\Models\Friendship;

$factory->define(Friendship::class, function () {
    $userIds = User::pluck('id')->toArray();
    $userId = fake()->randomElement($userIds);
    do {
        $friendId = fake()->randomElement($userIds);
    } while ($friendId === $userId);

    return [
        'user_id' => $userId,
        'friend_user_id' => $friendId,
        'status' => 'pending',
        'request_date' => now(),
        'response_date' => null,
    ];
});

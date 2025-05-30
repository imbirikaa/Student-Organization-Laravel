<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Get all users and events
        $users = User::all();
        $events = Event::all();

        foreach ($users as $user) {
            // Attach 1 to 3 random events for each user
            $randomEvents = $events->random(rand(1, 3))->pluck('id')->toArray();

            // Attach events to user
            $user->events()->syncWithoutDetaching($randomEvents);
        }
    }
}

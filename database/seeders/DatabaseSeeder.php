<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\University;
use App\Models\Department;
use App\Models\Community;
use App\Models\CommunityRole;
use App\Models\CommunityMembership;
use App\Models\Event;
use App\Models\Certificate;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use App\Models\QuizSubmission;
use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\ForumPost;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\Notification;
use App\Models\SystemLog;
use App\Models\UserRole;
use App\Models\UserBadge;
use App\Models\UserCertificate;
use App\Models\Badge;
use App\Models\ChatRoomUser;
use App\Models\City;
use App\Models\Friendship;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 🧱 Independent tables
        $roles = Role::factory()->count(5)->create();

        $roles = Role::all();

        User::all()->each(function ($user) use ($roles) {
            $assigned = $roles->random(rand(1, 2));

            foreach ($assigned as $role) {
                UserRole::firstOrCreate(
                    ['user_id' => $user->id, 'role_id' => $role->id],
                    ['assigned_date' => now()]
                );
            }
        });
        City::factory(10)->create();
        User::factory(50)->create();
        University::factory(5)->create();
        Department::factory(10)->create();
        Badge::factory(10)->create();
        CommunityRole::factory(5)->create();
        ForumCategory::factory(5)->create();
        ChatRoom::factory(10)->create();

        // 🧱 Tables with foreign keys
        Community::factory(10)->create();
        CommunityMembership::factory(30)->create();
        Event::factory(20)->create();
        Certificate::factory(30)->create();

        // 🧠 Quiz structure
        Quiz::factory(10)->create();
        QuizQuestion::factory(50)->create();
        QuizAnswer::factory(200)->create();
        QuizSubmission::factory(30)->create();

        // 🗣️ Forum
        ForumTopic::factory(20)->create();
        ForumPost::factory(50)->create();

        // 💬 Chat
        $chatRooms = ChatRoom::all();
        $users = User::all();

        foreach ($chatRooms as $room) {
            $assignedUsers = $users->random(rand(2, 5)); // randomly assign 2-5 users
            foreach ($assignedUsers as $user) {
                ChatRoomUser::firstOrCreate(
                    ['chat_room_id' => $room->id, 'user_id' => $user->id],
                    ['role' => 'member']
                );
            }
        }
        Message::factory(100)->create();

        // 🎫 Support
        SupportTicket::factory(20)->create();
        SupportTicketMessage::factory(40)->create();

        // 🔔 System
        Notification::factory(50)->create();
        SystemLog::factory(30)->create();

        // 🎖️ User Relations
        // UserRole::factory(20)->create();

        UserCertificate::factory(20)->create();

        $this->call([
            FriendshipSeeder::class,
            SingleUserSeeder::class,
            UserBadgeSeeder::class,
            EventUserSeeder::class,
        ]);
    }
}

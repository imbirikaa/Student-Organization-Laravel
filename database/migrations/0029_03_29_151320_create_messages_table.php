<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ChatRoom;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(ChatRoom::class, 'room_id')->constrained('chat_rooms'); // Doğru tablo adı
            $table->foreignIdFor(User::class, 'sender_user_id')->constrained('users');
            $table->text('message')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

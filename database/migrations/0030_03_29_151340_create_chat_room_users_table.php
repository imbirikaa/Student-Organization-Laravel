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
        Schema::create('chat_room_users', function (Blueprint $table) {
            $table->foreignIdFor(ChatRoom::class, 'chat_room_id')->constrained(); // Doğru sütun adı
            $table->foreignIdFor(User::class, 'user_id')->constrained();
            $table->string('role', 50)->nullable();
            $table->primary(['chat_room_id', 'user_id']); // chat_room_id kullan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_room_users');
    }
};

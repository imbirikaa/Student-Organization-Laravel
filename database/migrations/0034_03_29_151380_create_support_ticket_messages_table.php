<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SupportTicket;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(SupportTicket::class, 'ticket_id')->constrained('support_tickets'); // Doğru tablo adı
            $table->foreignIdFor(User::class, 'sender_user_id')->constrained('users');
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_visits', function (Blueprint $table) {
            $table->bigIncrements('id');

            
            $table->foreignIdFor(User::class, 'user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
                    

            
            $table->foreignIdFor(User::class, 'visitor_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
  

            
            $table->dateTime('visited_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_visits');
    }
};

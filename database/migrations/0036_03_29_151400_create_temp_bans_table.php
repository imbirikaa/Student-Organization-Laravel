<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('temp_bans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(User::class, 'user_id')->constrained();
            $table->text('reason')->nullable();
            $table->dateTime('ban_start')->nullable()->useCurrent();
            $table->dateTime('ban_end')->nullable();
            $table->boolean('is_active')->nullable()->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_bans');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable()->unique();
            $table->string('password');
            $table->date('birth_date')->nullable();
            $table->string('school')->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->date('graduate_date')->nullable();
            $table->string('nickname', 100)->unique();
            $table->text('about')->nullable();
            $table->string('profile_picture')->nullable();
            $table->dateTime('membership_date')->nullable();
            $table->boolean('email_verified')->nullable()->default(false);
            $table->boolean('phone_verified')->nullable()->default(false);
            $table->boolean('is_active')->nullable()->default(true);
            $table->softDeletes(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

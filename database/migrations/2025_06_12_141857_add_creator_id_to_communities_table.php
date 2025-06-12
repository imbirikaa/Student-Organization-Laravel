<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        // In the new migration file (e.g., xxxx_xx_xx_xxxxxx_add_creator_id_to_communities_table.php)
    public function up(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->foreignId('creator_id')->nullable()->constrained('users')->onDelete('set null')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropColumn('creator_id');
        });
    }
};

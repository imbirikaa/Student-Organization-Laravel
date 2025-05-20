<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CommunityRole;
use App\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_role_permissions', function (Blueprint $table) {
            $table->foreignIdFor(CommunityRole::class)->constrained();
            $table->foreignIdFor(Permission::class)->constrained();
            $table->primary(['community_role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_role_permissions');
    }
};

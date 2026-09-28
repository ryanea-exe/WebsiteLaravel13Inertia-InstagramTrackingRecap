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
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo')->nullable();
            $table->string('role')->default('Staff')->index();
            $table->string('status')->default('Aktif')->index();
            $table->timestamp('last_login_at')->nullable();
            $table->foreignId('seksi_id')->nullable()->constrained('seksis')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['seksi_id']);
            $table->dropColumn(['photo', 'role', 'status', 'last_login_at', 'seksi_id']);
        });
    }
};

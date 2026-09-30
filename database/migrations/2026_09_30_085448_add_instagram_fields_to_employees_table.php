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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'instagram_user_id')) {
                $table->string('instagram_user_id')->nullable()->unique();
            }
            if (!Schema::hasColumn('employees', 'instagram_username')) {
                $table->string('instagram_username')->nullable();
            }
            if (!Schema::hasColumn('employees', 'instagram_link_status')) {
                $table->string('instagram_link_status')->default('UNLINKED');
            }
            if (!Schema::hasColumn('employees', 'instagram_linked_at')) {
                $table->timestamp('instagram_linked_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'instagram_linked_at')) {
                $table->dropColumn('instagram_linked_at');
            }
            if (Schema::hasColumn('employees', 'instagram_link_status')) {
                $table->dropColumn('instagram_link_status');
            }
            // Do not drop instagram_user_id and instagram_username because they were already in the original migration in Step 31
        });
    }
};

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
        Schema::table('instagram_accounts', function (Blueprint $table) {
            $table->string('facebook_page_id')->nullable()->change();
        });

        Schema::table('instagram_media_metric_snapshots', function (Blueprint $table) {
            $table->renameColumn('impressions', 'views');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instagram_media_metric_snapshots', function (Blueprint $table) {
            $table->renameColumn('views', 'impressions');
        });

        Schema::table('instagram_accounts', function (Blueprint $table) {
            $table->string('facebook_page_id')->nullable(false)->change();
        });
    }
};

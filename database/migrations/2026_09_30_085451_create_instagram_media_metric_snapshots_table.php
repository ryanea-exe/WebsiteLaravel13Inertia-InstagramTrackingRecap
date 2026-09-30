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
        Schema::create('instagram_media_metric_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instagram_media_id')->constrained('instagram_media')->cascadeOnDelete();
            $table->timestamp('captured_at')->index();
            $table->bigInteger('likes')->nullable();
            $table->bigInteger('comments_count')->nullable();
            $table->bigInteger('shares')->nullable();
            $table->bigInteger('saves')->nullable();
            $table->bigInteger('reach')->nullable();
            $table->bigInteger('impressions')->nullable();
            $table->jsonb('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_media_metric_snapshots');
    }
};

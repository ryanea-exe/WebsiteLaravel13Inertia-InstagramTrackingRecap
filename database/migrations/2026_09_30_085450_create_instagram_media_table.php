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
        Schema::create('instagram_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instagram_account_id')->constrained()->cascadeOnDelete();
            $table->string('external_media_id')->unique();
            $table->string('media_type');
            $table->string('product_type')->nullable();
            $table->text('caption')->nullable();
            $table->text('permalink');
            $table->text('media_url')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->timestamp('published_at');
            $table->jsonb('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_media');
    }
};

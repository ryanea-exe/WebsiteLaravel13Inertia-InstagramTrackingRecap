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
        Schema::create('instagram_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instagram_media_id')->constrained('instagram_media')->cascadeOnDelete();
            $table->string('external_comment_id')->unique();
            $table->string('commenter_instagram_user_id')->nullable()->index();
            $table->string('commenter_username')->nullable();
            $table->foreignId('matched_employee_id')->nullable()->constrained('employees')->restrictOnDelete();
            $table->text('text')->nullable();
            $table->timestamp('commented_at')->index();
            $table->timestamp('deleted_at')->nullable()->index();
            $table->jsonb('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_comments');
    }
};

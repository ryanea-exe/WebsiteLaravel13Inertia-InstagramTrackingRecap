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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instagram_account_id')->constrained('instagram_accounts')->cascadeOnDelete();
            $table->string('sync_type');
            $table->string('status');
            $table->timestamp('started_at')->index();
            $table->timestamp('finished_at')->nullable();
            $table->integer('records_processed')->default(0);
            $table->text('error_message')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};

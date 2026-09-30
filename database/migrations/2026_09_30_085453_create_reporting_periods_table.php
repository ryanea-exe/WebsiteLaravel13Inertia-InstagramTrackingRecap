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
        Schema::create('reporting_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('start_at')->index();
            $table->timestamp('end_at')->index();
            $table->string('timezone');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reporting_periods');
    }
};

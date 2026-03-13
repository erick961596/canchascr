<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('court_id')->constrained()->cascadeOnDelete();
            $table->enum('day_of_week', ['mon','tue','wed','thu','fri','sat','sun']);
            $table->time('open_time');
            $table->time('close_time');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['court_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};

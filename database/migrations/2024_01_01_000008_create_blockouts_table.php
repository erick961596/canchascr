<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blockouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('court_id')->constrained()->cascadeOnDelete();
            $table->date('block_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('full_day')->default(false);
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['court_id', 'block_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blockouts');
    }
};

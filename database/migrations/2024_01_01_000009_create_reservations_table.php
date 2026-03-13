<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('court_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending','confirmed','cancelled','no_show'])->default('pending');
            $table->string('payment_proof')->nullable();
            $table->enum('payment_status', ['pending','verified','rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('sinpe_reference')->nullable();
            $table->timestamps();

            $table->index(['court_id', 'reservation_date', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};

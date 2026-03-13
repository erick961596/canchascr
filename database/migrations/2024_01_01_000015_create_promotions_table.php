<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('venue_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed']); // % o monto fijo
            $table->decimal('value', 10, 2);               // 20 = 20% o ₡2000 fijo
            $table->date('starts_at');
            $table->date('ends_at');
            $table->json('court_ids')->nullable();          // null = aplica a todas las canchas del venue
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['venue_id', 'active', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};

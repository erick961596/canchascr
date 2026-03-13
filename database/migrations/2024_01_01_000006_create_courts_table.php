<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('courts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('venue_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('sport', ['futbol','basquetbol','tenis','padel','volleyball','beisbol','otro'])->default('futbol');
            $table->decimal('price_per_hour', 10, 2);
            $table->unsignedInteger('slot_duration')->default(60);
            $table->json('features')->nullable();
            $table->json('images')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['venue_id', 'sport', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};

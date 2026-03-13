<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('province')->nullable();
            $table->string('canton')->nullable();
            $table->string('district')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('logo')->nullable();
            $table->json('images')->nullable();
            $table->json('amenities')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();

            $table->index(['province', 'canton', 'district']);
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};

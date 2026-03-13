<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('venue_ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('venue_id');
            $table->uuid('user_id');
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['venue_id', 'user_id']); // one rating per user per venue
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_ratings');
    }
};

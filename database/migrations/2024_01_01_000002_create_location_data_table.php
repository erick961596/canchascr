<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('location_data', function (Blueprint $table) {
            $table->id();
            $table->string('province');
            $table->string('canton');
            $table->string('district');
            $table->index(['province', 'canton']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_data');
    }
};

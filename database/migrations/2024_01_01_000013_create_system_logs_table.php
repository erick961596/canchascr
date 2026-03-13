<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('level', 20);       // info, warning, error, payment, auth, subscription
            $table->string('type', 60);        // payment_success, subscription_activated, login_failed...
            $table->uuid('user_id')->nullable();
            $table->string('subject')->nullable(); // brief description
            $table->json('context')->nullable();   // full payload/data
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['level', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index('user_id');
        });
    }
    public function down(): void { Schema::dropIfExists('system_logs'); }
};

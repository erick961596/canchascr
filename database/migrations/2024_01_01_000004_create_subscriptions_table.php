<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained();
            $table->enum('status', ['active','pending','incomplete','past_due','failed','canceled'])->default('pending');
            $table->decimal('price', 10, 2);
            $table->string('payment_method')->default('manual')->comment('card|manual');
            $table->string('payment_proof')->nullable()->comment('S3 path comprobante SINPE');
            $table->string('onvo_subscription_id')->nullable();
            $table->string('onvo_payment_intent_id')->nullable();
            $table->string('onvo_payment_method_id')->nullable();
            $table->string('onvo_period_end')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['card', 'manual'])->default('manual');
            $table->enum('status', ['pending', 'succeeded', 'rejected'])->default('pending');
            $table->string('proof_path')->nullable()->comment('S3 path');
            $table->string('onvo_payment_intent_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscriptions');
    }
};

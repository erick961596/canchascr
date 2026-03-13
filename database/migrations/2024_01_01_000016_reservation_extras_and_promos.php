<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Servicios adicionales elegidos en una reserva
        Schema::create('reservation_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('extra_service_id')->constrained('extra_services')->cascadeOnDelete();
            $table->decimal('price_snapshot', 10, 2); // precio al momento de reservar
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        // Columnas nuevas en reservations
        Schema::table('reservations', function (Blueprint $table) {
            // Reservas manuales (owner crea para cliente)
            $table->string('client_name')->nullable()->after('notes');
            $table->string('client_phone')->nullable()->after('client_name');
            $table->boolean('is_manual')->default(false)->after('client_phone');
            // Descuento aplicado
            $table->foreignUuid('promotion_id')->nullable()->after('is_manual')->constrained('promotions')->nullOnDelete();
            $table->decimal('discount_amount', 10, 2)->default(0)->after('promotion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_services');
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['client_name','client_phone','is_manual','promotion_id','discount_amount']);
        });
    }
};

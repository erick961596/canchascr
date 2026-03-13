<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // onvopay_id already exists — just make sure it's there, add price_id alias column
            if (!Schema::hasColumn('plans', 'onvopay_price_id')) {
                $table->string('onvopay_price_id')->nullable()->after('onvopay_id')
                      ->comment('Price ID from ONVO Pay dashboard e.g. cmklig02j32j5js200psne5v6');
            }
        });
    }
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('onvopay_price_id');
        });
    }
};

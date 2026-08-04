<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('readings', function (Blueprint $table) {
            $table->string('device')->nullable()->after('point');
            $table->string('fw_version', 30)->nullable()->after('device');
            $table->string('connection', 20)->nullable()->after('fw_version');
            $table->unsignedBigInteger('uptime_ms')->nullable()->after('connection');
            $table->decimal('rainfall_24h', 10, 4)->nullable()->after('rainfall');
            $table->decimal('humidity', 6, 2)->nullable()->after('temperature');
            $table->smallInteger('wifi_rssi')->nullable()->after('wind_speed');
            $table->smallInteger('gsm_signal')->nullable()->after('wifi_rssi');
            $table->json('raw_payload')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('readings', function (Blueprint $table) {
            $table->dropColumn(['device', 'fw_version', 'connection', 'uptime_ms', 'rainfall_24h', 'humidity', 'wifi_rssi', 'gsm_signal', 'raw_payload']);
        });
    }
};

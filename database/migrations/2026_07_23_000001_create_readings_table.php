<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('readings', function (Blueprint $table) {
            $table->id();
            $table->string('point', 20)->index();
            $table->decimal('water_level', 8, 2);
            $table->decimal('rainfall', 8, 2);
            $table->decimal('temperature', 5, 2);
            $table->decimal('wind_speed', 6, 2);
            $table->unsignedTinyInteger('risk_index');
            $table->string('status', 20)->index();
            $table->timestamp('recorded_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('readings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['temperature', 'humidity', 'air_quality'])->default('temperature');
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unit')->default('°C');
            $table->decimal('min_threshold', 8, 2)->nullable();
            $table->decimal('max_threshold', 8, 2)->nullable();
            $table->boolean('alert_enabled')->default(true);
            $table->string('api_key', 64)->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')->constrained()->cascadeOnDelete();
            $table->decimal('value', 10, 2);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['sensor_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
        Schema::dropIfExists('sensors');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['light', 'fan', 'plug', 'other'])->default('other');
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mqtt_topic')->nullable();
            $table->boolean('is_on')->default(false);
            $table->boolean('is_online')->default(true);
            $table->string('api_key', 64)->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('command', ['on', 'off', 'toggle']);
            $table->string('source')->default('web');
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_commands');
        Schema::dropIfExists('devices');
    }
};

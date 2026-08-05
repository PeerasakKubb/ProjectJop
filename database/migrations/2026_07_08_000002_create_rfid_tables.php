<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfid_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('card_uid')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('rfid_readers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location')->nullable();
            $table->string('api_key', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rfid_reader_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('class_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('scanned_at');
            $table->enum('type', ['in', 'out'])->default('in');
            $table->enum('status', ['present', 'late', 'absent'])->default('present');
            $table->timestamps();

            $table->index(['user_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('class_sessions');
        Schema::dropIfExists('rfid_readers');
        Schema::dropIfExists('rfid_cards');
    }
};

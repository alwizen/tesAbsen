<?php
// database/migrations/2024_01_01_000002_create_work_schedules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->time('check_in_time'); // Jam masuk standar
            $table->time('check_out_time'); // Jam pulang standar
            $table->integer('grace_period_minutes')->default(0); // Toleransi keterlambatan (menit)
            $table->boolean('is_overnight')->default(false); // Shift melewati hari
            $table->integer('max_work_hours')->default(8); // Maksimal jam kerja normal
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Satu departemen hanya bisa punya satu jadwal aktif
            $table->unique(['department_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};

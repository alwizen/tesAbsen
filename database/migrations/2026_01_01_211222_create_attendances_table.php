<?php
// database/migrations/2024_01_01_000004_create_attendances_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('work_date'); // Tanggal kerja (bukan tanggal tap)
            $table->dateTime('check_in_at')->nullable(); // Waktu tap masuk
            $table->dateTime('check_out_at')->nullable(); // Waktu tap pulang
            $table->integer('late_minutes')->default(0); // Keterlambatan dalam menit
            $table->decimal('work_hours', 5, 2)->default(0); // Total jam kerja
            $table->enum('status', ['present', 'late', 'absent', 'incomplete'])->default('incomplete');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Satu karyawan hanya bisa punya satu record per tanggal kerja
            $table->unique(['employee_id', 'work_date']);
            $table->index(['employee_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

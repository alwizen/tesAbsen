<?php
// database/migrations/2024_01_01_000007_create_payroll_details_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('attendance_summary_id')->nullable()->constrained();

            // Data absensi
            $table->integer('total_work_days')->default(0);
            $table->decimal('total_work_hours', 8, 2)->default(0);
            $table->integer('total_late_minutes')->default(0);

            // Komponen gaji
            $table->decimal('daily_rate', 12, 2)->default(0); // Tarif per hari
            $table->decimal('hourly_rate', 12, 2)->default(0); // Tarif per jam
            $table->decimal('base_salary', 12, 2)->default(0); // Gaji pokok
            $table->decimal('late_deduction', 12, 2)->default(0); // Potongan telat
            $table->decimal('other_deductions', 12, 2)->default(0); // Potongan lain
            $table->decimal('allowances', 12, 2)->default(0); // Tunjangan
            $table->decimal('bonuses', 12, 2)->default(0); // Bonus
            $table->decimal('net_salary', 12, 2)->default(0); // Gaji bersih

            $table->text('calculation_notes')->nullable();
            $table->timestamps();

            $table->unique(['payroll_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};

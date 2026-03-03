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

            // Snapshot aturan gaji dari department
            $table->enum('salary_type', ['daily', 'hourly'])->nullable();
            $table->decimal('daily_rate', 12, 2)->default(0);
            $table->decimal('hourly_rate', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('pj_allowance', 12, 2)->default(0);

            // Data absensi
            $table->integer('total_work_days')->default(0);
            $table->decimal('total_work_hours', 8, 2)->default(0);
            $table->integer('total_late_minutes')->default(0);

            // Perhitungan gaji
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('late_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('bonuses', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);

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

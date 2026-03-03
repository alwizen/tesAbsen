<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->year('year');
            $table->tinyInteger('month'); // 1-12
            $table->integer('total_present')->default(0); // Total hadir
            $table->integer('total_late')->default(0); // Total terlambat
            $table->integer('total_absent')->default(0); // Total tidak hadir
            $table->decimal('total_work_hours', 8, 2)->default(0); // Total jam kerja
            $table->integer('total_late_minutes')->default(0); // Total menit terlambat
            $table->timestamps();

            $table->unique(['employee_id', 'year', 'month']);
            $table->index(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_summaries');
    }
};

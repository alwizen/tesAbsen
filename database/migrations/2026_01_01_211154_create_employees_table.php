<?php
// database/migrations/2024_01_01_000003_create_employees_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('rfid_number')->unique(); // Nomor RFID
            $table->string('employee_number')->unique(); // NIK/No Karyawan
            $table->string('name');
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->date('join_date')->nullable();
            // $table->date('resign_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('rfid_number'); // Index untuk pencarian cepat saat tap
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

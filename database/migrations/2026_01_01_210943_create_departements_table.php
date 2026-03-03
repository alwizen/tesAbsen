<?php
// database/migrations/2024_01_01_000001_create_departments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            // Info dasar
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            // Master penggajian
            $table->enum('salary_type', ['daily', 'hourly'])
                ->default('daily')
                ->comment('daily = gaji per hari, hourly = gaji per jam');

            $table->decimal('daily_rate', 12, 2)->default(0);
            $table->decimal('hourly_rate', 12, 2)->default(0);

            // Tunjangan
            $table->decimal('allowance', 12, 2)->default(0)
                ->comment('Tunjangan tetap per periode payroll');

            // Tambahan khusus PJ / Ketua Tim
            $table->decimal('pj_allowance', 12, 2)->default(0)
                ->comment('Tambahan gaji untuk ketua tim / PJ');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};

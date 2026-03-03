<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('photo_in_path')->nullable()->after('check_in_at');
            $table->decimal('location_in_lat', 10, 8)->nullable()->after('photo_in_path');
            $table->decimal('location_in_lng', 11, 8)->nullable()->after('location_in_lat');

            $table->string('photo_out_path')->nullable()->after('check_out_at');
            $table->decimal('location_out_lat', 10, 8)->nullable()->after('photo_out_path');
            $table->decimal('location_out_lng', 11, 8)->nullable()->after('location_out_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'photo_in_path',
                'location_in_lat',
                'location_in_lng',
                'photo_out_path',
                'location_out_lat',
                'location_out_lng',
            ]);
        });
    }
};
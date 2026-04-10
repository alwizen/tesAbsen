<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\WorkSchedule;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Filament Users
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Supervisor Dapur',
            'email' => 'supervisor@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Departments (Master Penggajian)
        |--------------------------------------------------------------------------
        */

        // --- Aslap ---
        $aslap = Department::create([
            'name' => 'Asisten Lapangan',
            'code' => 'ASLAP-01',
            'description' => 'Asisten Lapangan',
            'is_active' => true,
            'salary_type' => 'daily',
            'daily_rate' => 200000,
            'hourly_rate' => 0,
            'allowance' => 25000,
            'pj_allowance' => 0,
        ]);

        // --- Ahli Gizi ---
        $ahliGizi = Department::create([
            'name' => 'Ahli Gizi',
            'code' => 'GIZI-01',
            'description' => 'Ahli gizi',
            'is_active' => true,
            'salary_type' => 'daily',
            'daily_rate' => 200000,
            'hourly_rate' => 0,
            'allowance' => 25000,
            'pj_allowance' => 0,
        ]);

        // --- Akuntan ---
        $akuntan = Department::create([
            'name' => 'Akuntan',
            'code' => 'AKUN-01',
            'description' => 'Akuntan',
            'is_active' => true,
            'salary_type' => 'daily',
            'daily_rate' => 200000,
            'hourly_rate' => 0,
            'allowance' => 25000,
            'pj_allowance' => 0,
        ]);

        // --- Pengolahan ---
        $koorPengolahan = Department::create([
            'name' => 'Koor Pengolahan',
            'code' => 'COOK-KOOR',
            'description' => 'Koordinator tim pengolahan dan memasak',
            'is_active' => true,
            'salary_type' => 'daily',
            'daily_rate' => 150000,
            'hourly_rate' => 0,
            'allowance' => 15000,
            'pj_allowance' => 20000,
        ]);

        $timPengolahan = Department::create([
            'name' => 'Pengolahan',
            'code' => 'COOK-01',
            'description' => 'Tim pengolahan dan memasak',
            'is_active' => true,
            'salary_type' => 'daily',
            'daily_rate' => 130000,
            'hourly_rate' => 0,
            'allowance' => 15000,
            'pj_allowance' => 0,
        ]);

        // --- Persiapan ---
        $koorPersiapan = Department::create([
            'name' => 'Koor Persiapan',
            'code' => 'PREP-KOOR',
            'description' => 'Koordinator tim persiapan bahan makanan',
            'is_active' => true,
            'salary_type' => 'daily',
            'daily_rate' => 140000,
            'hourly_rate' => 0,
            'allowance' => 10000,
            'pj_allowance' => 15000,
        ]);

        $timPersiapan = Department::create([
            'name' => 'Persiapan',
            'code' => 'PREP-01',
            'description' => 'Tim persiapan bahan makanan',
            'is_active' => true,
            'salary_type' => 'daily',
            'daily_rate' => 120000,
            'hourly_rate' => 0,
            'allowance' => 10000,
            'pj_allowance' => 0,
        ]);

        // --- Distribusi ---
        $koorDistribusi = Department::create([
            'name' => 'Koor Distribusi',
            'code' => 'DIST-KOOR',
            'description' => 'Koordinator tim distribusi makanan',
            'is_active' => true,
            'salary_type' => 'daily',
            'daily_rate' => 135000,
            'hourly_rate' => 0,
            'allowance' => 10000,
            'pj_allowance' => 15000,
        ]);

        $timDistribusi = Department::create([
            'name' => 'Distribusi',
            'code' => 'DIST-01',
            'description' => 'Tim distribusi makanan',
            'is_active' => true,
            'salary_type' => 'daily',
            'daily_rate' => 115000,
            'hourly_rate' => 0,
            'allowance' => 10000,
            'pj_allowance' => 0,
        ]);

        // --- Packing & Pemorsian ---
        $koorPacking = Department::create([
            'name' => 'Koor Packing & Pemorsian',
            'code' => 'PORT-KOOR',
            'description' => 'Koordinator tim packing dan pemorsian makanan',
            'is_active' => true,
            'salary_type' => 'daily',
            'daily_rate' => 130000,
            'hourly_rate' => 0,
            'allowance' => 10000,
            'pj_allowance' => 15000,
        ]);

        $timPacking = Department::create([
            'name' => 'Packing & Pemorsian',
            'code' => 'PORT-01',
            'description' => 'Tim packing dan pemorsian makanan',
            'is_active' => true,
            'salary_type' => 'daily',
            'daily_rate' => 110000,
            'hourly_rate' => 0,
            'allowance' => 10000,
            'pj_allowance' => 0,
        ]);

        // --- Cuci Ompreng ---
        $koorCuci = Department::create([
            'name' => 'Koor Cuci Ompreng',
            'code' => 'WASH-KOOR',
            'description' => 'Koordinator tim cuci peralatan',
            'is_active' => true,
            'salary_type' => 'hourly',
            'daily_rate' => 0,
            'hourly_rate' => 18000,
            'allowance' => 5000,
            'pj_allowance' => 10000,
        ]);

        $timCuci = Department::create([
            'name' => 'Cuci Ompreng',
            'code' => 'WASH-01',
            'description' => 'Tim cuci peralatan',
            'is_active' => true,
            'salary_type' => 'hourly',
            'daily_rate' => 0,
            'hourly_rate' => 15000,
            'allowance' => 5000,
            'pj_allowance' => 0,
        ]);

        // --- Cleaning Service ---
        $timKebersihan = Department::create([
            'name' => 'Cleaning Service',
            'code' => 'CLEAN-01',
            'description' => 'Tim petugas kebersihan',
            'is_active' => true,
            'salary_type' => 'hourly',
            'daily_rate' => 0,
            'hourly_rate' => 14000,
            'allowance' => 5000,
            'pj_allowance' => 0,
        ]);

        // --- Keamanan ---
        $timKeamanan = Department::create([
            'name' => 'Keamanan',
            'code' => 'SEC-01',
            'description' => 'Tim keamanan',
            'is_active' => true,
            'salary_type' => 'daily',
            'daily_rate' => 120000,
            'hourly_rate' => 0,
            'allowance' => 10000,
            'pj_allowance' => 0,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Work Schedules
        |--------------------------------------------------------------------------
        */

        // Aslap
        WorkSchedule::create([
            'department_id' => $aslap->id,
            'check_in_time' => '07:00:00',
            'check_out_time' => '18:00:00',
            'grace_period_minutes' => 10,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);


        // Ahli Gizi
        WorkSchedule::create([
            'department_id' => $ahliGizi->id,
            'check_in_time' => '07:00:00',
            'check_out_time' => '15:00:00',
            'grace_period_minutes' => 10,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        // Akuntan
        WorkSchedule::create([
            'department_id' => $akuntan->id,
            'check_in_time' => '07:00:00',
            'check_out_time' => '15:00:00',
            'grace_period_minutes' => 10,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        // Koor Pengolahan
        WorkSchedule::create([
            'department_id' => $koorPengolahan->id,
            'check_in_time' => '00:00:00',
            'check_out_time' => '08:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        // Pengolahan
        WorkSchedule::create([
            'department_id' => $timPengolahan->id,
            'check_in_time' => '00:00:00',
            'check_out_time' => '08:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        // Koor Persiapan
        WorkSchedule::create([
            'department_id' => $koorPersiapan->id,
            'check_in_time' => '19:00:00',
            'check_out_time' => '03:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => true,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        // Persiapan
        WorkSchedule::create([
            'department_id' => $timPersiapan->id,
            'check_in_time' => '19:00:00',
            'check_out_time' => '03:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => true,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        // Koor Distribusi
        WorkSchedule::create([
            'department_id' => $koorDistribusi->id,
            'check_in_time' => '06:00:00',
            'check_out_time' => '14:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        // Distribusi
        WorkSchedule::create([
            'department_id' => $timDistribusi->id,
            'check_in_time' => '06:00:00',
            'check_out_time' => '14:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        // Koor Packing & Pemorsian
        WorkSchedule::create([
            'department_id' => $koorPacking->id,
            'check_in_time' => '04:00:00',
            'check_out_time' => '12:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        // Packing & Pemorsian
        WorkSchedule::create([
            'department_id' => $timPacking->id,
            'check_in_time' => '04:00:00',
            'check_out_time' => '12:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        // Koor Cuci Ompreng
        WorkSchedule::create([
            'department_id' => $koorCuci->id,
            'check_in_time' => '11:00:00',
            'check_out_time' => '19:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        // Cuci Ompreng
        WorkSchedule::create([
            'department_id' => $timCuci->id,
            'check_in_time' => '11:00:00',
            'check_out_time' => '19:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        // Cleaning Service
        WorkSchedule::create([
            'department_id' => $timKebersihan->id,
            'check_in_time' => '11:00:00',
            'check_out_time' => '19:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'max_work_hours' => 8,
            'is_active' => true,
        ]);

        // Keamanan
        WorkSchedule::create([
            'department_id' => $timKeamanan->id,
            'check_in_time' => '07:00:00',
            'check_out_time' => '19:00:00',
            'grace_period_minutes' => 15,
            'is_overnight' => false,
            'max_work_hours' => 12,
            'is_active' => true,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Employees
        |--------------------------------------------------------------------------
        */

        Employee::insert([
            // Aslap
            [
                'rfid_number' => '0001',
                'employee_number' => 'ASLAP001',
                'name' => 'Budi Doremi',
                'department_id' => $aslap->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '0001',
            ],
            // Ahli Gizi
            [
                'rfid_number' => '1',
                'employee_number' => 'GIZI001',
                'name' => 'Sari Dewi',
                'department_id' => $ahliGizi->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '001',
            ],
            // Akuntan
            [
                'rfid_number' => '2',
                'employee_number' => 'AKUN001',
                'name' => 'Rini Marlina',
                'department_id' => $akuntan->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '002',
            ],
            // Koor Pengolahan
            [
                'rfid_number' => '3',
                'employee_number' => 'COOK-KOOR001',
                'name' => 'Ahmad Wijaya',
                'department_id' => $koorPengolahan->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '003',
            ],
            // Pengolahan
            [
                'rfid_number' => '4',
                'employee_number' => 'COOK001',
                'name' => 'Dewi Lestari',
                'department_id' => $timPengolahan->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '004',
            ],
            // Koor Persiapan
            [
                'rfid_number' => '5',
                'employee_number' => 'PREP-KOOR001',
                'name' => 'Budi Santoso',
                'department_id' => $koorPersiapan->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '005',
            ],
            // Persiapan
            [
                'rfid_number' => '6',
                'employee_number' => 'PREP001',
                'name' => 'Siti Aminah',
                'department_id' => $timPersiapan->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '006',
            ],
            // Distribusi
            [
                'rfid_number' => '7',
                'employee_number' => 'DIST001',
                'name' => 'Fitri Handayani',
                'department_id' => $timDistribusi->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '007',
            ],
            // Koor Distribusi
            [
                'rfid_number' => '8',
                'employee_number' => 'DIST-KOOR001',
                'name' => 'Eko Prasetyo',
                'department_id' => $koorDistribusi->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '008',
            ],
            // Koor Packing & Pemorsian
            [
                'rfid_number' => '9',
                'employee_number' => 'PORT-KOOR001',
                'name' => 'Gunawan Setiawan',
                'department_id' => $koorPacking->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '009',
            ],
            // Packing & Pemorsian
            [
                'rfid_number' => '10',
                'employee_number' => 'PORT001',
                'name' => 'Hendra Kusuma',
                'department_id' => $timPacking->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '010',
            ],
            // Koor Cuci Ompreng
            [
                'rfid_number' => '11',
                'employee_number' => 'WASH-KOOR001',
                'name' => 'Indah Permata',
                'department_id' => $koorCuci->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '011',
            ],
            // Cuci Ompreng
            [
                'rfid_number' => '12',
                'employee_number' => 'WASH001',
                'name' => 'Joko Susilo',
                'department_id' => $timCuci->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '012',
            ],
            // Cleaning Service
            [
                'rfid_number' => '13',
                'employee_number' => 'CLEAN001',
                'name' => 'Kartika Sari',
                'department_id' => $timKebersihan->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '013',
            ],
            // Keamanan
            [
                'rfid_number' => '14',
                'employee_number' => 'SEC001',
                'name' => 'Lukman Hakim',
                'department_id' => $timKeamanan->id,
                'join_date' => '2024-01-01',
                'is_active' => true,
                'phone' => '014',
            ],
        ]);
    }
}
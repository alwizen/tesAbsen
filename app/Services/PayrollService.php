<?php
// app/Services/PayrollService.php

namespace App\Services;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\PayrollDetail;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Generate payroll untuk bulan tertentu
     * 
     * Contoh perhitungan (bisa disesuaikan dengan kebutuhan):
     * - Gaji berdasarkan total hari kerja * tarif harian
     * - ATAU berdasarkan total jam kerja * tarif per jam
     * - Potongan keterlambatan: total menit telat * tarif potongan per menit
     */
    public function generatePayroll(int $year, int $month, array $employees = []): Payroll
    {
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        // Create payroll record
        $payroll = Payroll::create([
            'year' => $year,
            'month' => $month,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => 'draft',
        ]);

        // Get employees
        $employeeQuery = Employee::with(['attendanceSummaries' => function ($query) use ($year, $month) {
            $query->where('year', $year)->where('month', $month);
        }])->where('is_active', true);

        if (!empty($employees)) {
            $employeeQuery->whereIn('id', $employees);
        }

        $employeeList = $employeeQuery->get();

        foreach ($employeeList as $employee) {
            $this->generatePayrollDetail($payroll, $employee);
        }

        return $payroll;
    }

    /**
     * Generate detail payroll untuk satu karyawan
     */
    private function generatePayrollDetail(Payroll $payroll, Employee $employee): void
    {
        $summary = $employee->attendanceSummaries()
            ->where('year', $payroll->year)
            ->where('month', $payroll->month)
            ->first();

        if (!$summary) {
            return; // Skip jika tidak ada summary
        }

        // Contoh tarif (dalam implementasi nyata, ini bisa diambil dari tabel rates atau employee settings)
        $dailyRate = 150000; // Rp 150.000 per hari
        $hourlyRate = 18750; // Rp 18.750 per jam (150k / 8 jam)
        $lateDeductionPerMinute = 1000; // Rp 1.000 per menit keterlambatan

        // Hitung gaji pokok berdasarkan total hari kerja
        $baseSalary = $summary->total_present * $dailyRate;

        // ATAU bisa juga berdasarkan jam kerja (pilih salah satu sesuai kebijakan)
        // $baseSalary = $summary->total_work_hours * $hourlyRate;

        // Hitung potongan keterlambatan
        $lateDeduction = $summary->total_late_minutes * $lateDeductionPerMinute;

        // Hitung gaji bersih
        $netSalary = $baseSalary - $lateDeduction;

        PayrollDetail::create([
            'payroll_id' => $payroll->id,
            'employee_id' => $employee->id,
            'attendance_summary_id' => $summary->id,
            'total_work_days' => $summary->total_present,
            'total_work_hours' => $summary->total_work_hours,
            'total_late_minutes' => $summary->total_late_minutes,
            'daily_rate' => $dailyRate,
            'hourly_rate' => $hourlyRate,
            'base_salary' => $baseSalary,
            'late_deduction' => $lateDeduction,
            'other_deductions' => 0,
            'allowances' => 0,
            'bonuses' => 0,
            'net_salary' => $netSalary,
            'calculation_notes' => "Gaji dihitung: {$summary->total_present} hari x Rp " . number_format($dailyRate, 0, ',', '.') . " - potongan telat {$summary->total_late_minutes} menit",
        ]);
    }

    /**
     * Process payroll - lock dan finalize perhitungan
     */
    public function processPayroll(Payroll $payroll, Employee $processedBy): Payroll
    {
        if ($payroll->status !== 'draft') {
            throw new \Exception('Payroll sudah diproses sebelumnya');
        }

        $payroll->update([
            'status' => 'processed',
            'processed_at' => now(),
            'processed_by' => $processedBy->id,
        ]);

        return $payroll;
    }

    /**
     * Mark payroll as paid
     */
    public function markAsPaid(Payroll $payroll): Payroll
    {
        if ($payroll->status !== 'processed') {
            throw new \Exception('Payroll harus diproses terlebih dahulu');
        }

        $payroll->update(['status' => 'paid']);

        return $payroll;
    }
}

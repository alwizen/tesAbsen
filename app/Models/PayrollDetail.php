<?php
// app/Models/PayrollDetail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDetail extends Model
{
    protected $fillable = [
        'payroll_id',
        'employee_id',
        'attendance_summary_id',

        // Snapshot aturan gaji
        'salary_type',
        'daily_rate',
        'hourly_rate',
        'allowances',
        'pj_allowance',

        // Data absensi
        'total_work_days',
        'total_work_hours',
        'total_late_minutes',

        // Perhitungan gaji
        'base_salary',
        'late_deduction',
        'other_deductions',
        'bonuses',
        'net_salary',

        'calculation_notes',
    ];

    protected $casts = [
        'total_work_hours' => 'decimal:2',

        // Snapshot
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'allowances' => 'decimal:2',
        'pj_allowance' => 'decimal:2',

        // Salary calc
        'base_salary' => 'decimal:2',
        'late_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    // Relations
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceSummary(): BelongsTo
    {
        return $this->belongsTo(AttendanceSummary::class);
    }

    // Helpers
    public function isDailySalary(): bool
    {
        return $this->salary_type === 'daily';
    }

    public function isHourlySalary(): bool
    {
        return $this->salary_type === 'hourly';
    }
}

<?php
// app/Models/AttendanceSummary.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSummary extends Model
{
    protected $fillable = [
        'employee_id',
        'payroll_id',
        'year',
        'month',
        'total_present',
        'total_late',
        'total_absent',
        'total_work_hours',
        'total_late_minutes',
    ];

    protected $casts = [
        'total_work_hours' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollDetails(): HasMany
    {
        return $this->hasMany(PayrollDetail::class);
    }
}

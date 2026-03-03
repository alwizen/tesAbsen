<?php
// app/Models/Employee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfid_number',
        'employee_number',
        'name',
        'department_id',
        'join_date',
        'resign_date',
        'is_active',
        'phone',
    ];

    protected $casts = [
        'join_date' => 'date',
        'resign_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceSummaries(): HasMany
    {
        return $this->hasMany(AttendanceSummary::class);
    }

    public function payrollDetails(): HasMany
    {
        return $this->hasMany(PayrollDetail::class);
    }

    /**
     * Cek apakah karyawan aktif pada tanggal tertentu
     */
    public function isActiveOn($date): bool
    {
        $checkDate = is_string($date) ?\Carbon\Carbon::parse($date) : $date;

        if (!$this->is_active) {
            return false;
        }

        if ($checkDate->lt($this->join_date)) {
            return false;
        }

        if ($this->resign_date && $checkDate->gt($this->resign_date)) {
            return false;
        }

        return true;
    }
}
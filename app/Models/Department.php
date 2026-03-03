<?php
// app/Models/Department.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',

        // Penggajian
        'salary_type',
        'daily_rate',
        'hourly_rate',
        'allowance',
        'pj_allowance',
    ];

    protected $casts = [
        'is_active' => 'boolean',

        // Penggajian
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'allowance' => 'decimal:2',
        'pj_allowance' => 'decimal:2',
    ];

    // Relations
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class);
    }

    public function activeWorkSchedule(): HasOne
    {
        return $this->hasOne(WorkSchedule::class)->where('is_active', true);
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

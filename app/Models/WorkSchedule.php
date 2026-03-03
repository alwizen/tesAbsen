<?php
// app/Models/WorkSchedule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class WorkSchedule extends Model
{
    protected $fillable = [
        'department_id',
        'check_in_time',
        'check_out_time',
        'grace_period_minutes',
        'is_overnight',
        'max_work_hours',
        'is_active',
    ];

    protected $casts = [
        'check_in_time' => 'datetime:H:i:s',
        'check_out_time' => 'datetime:H:i:s',
        'is_overnight' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Hitung keterlambatan dalam menit
     */
    public function calculateLateMinutes(Carbon $checkInTime): int
    {
        $scheduledTime = Carbon::parse($this->check_in_time);
        $actualTime = $checkInTime->copy()->setDate(
            $scheduledTime->year,
            $scheduledTime->month,
            $scheduledTime->day
        );

        $diff = $actualTime->diffInMinutes($scheduledTime, false);

        // Jika negatif berarti lebih awal, return 0
        // Jika kurang dari grace period, return 0
        if ($diff <= 0 || $diff <= $this->grace_period_minutes) {
            return 0;
        }

        return $diff;
    }

    /**
     * Tentukan tanggal kerja berdasarkan waktu check-in
     * Untuk shift malam, jika tap sebelum jam check_in, maka tanggal kerja = hari sebelumnya
     */
    public function determineWorkDate(Carbon $checkInTime): Carbon
    {
        if (!$this->is_overnight) {
            return $checkInTime->copy()->startOfDay();
        }

        $scheduledCheckIn = Carbon::parse($this->check_in_time);

        // Jika check-in sebelum jam tengah malam dan sebelum jam kerja scheduled,
        // kemungkinan ini adalah check-out dari shift kemarin
        if ($checkInTime->hour < 12 && $checkInTime->hour < $scheduledCheckIn->hour) {
            return $checkInTime->copy()->subDay()->startOfDay();
        }

        return $checkInTime->copy()->startOfDay();
    }
}

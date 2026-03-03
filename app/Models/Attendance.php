<?php
// app/Models/Attendance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'work_date',
        'check_in_at',
        'check_out_at',
        'late_minutes',
        'work_hours',
        'status',
        'notes',
        'photo_in_path',
        'location_in_lat',
        'location_in_lng',
        'photo_out_path',
        'location_out_lat',
        'location_out_lng',
    ];

    protected $casts = [
        'work_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'work_hours' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Hitung jam kerja berdasarkan check-in dan check-out
     */
    public function calculateWorkHours(): float
    {
        if (!$this->check_in_at || !$this->check_out_at) {
            return 0;
        }

        $checkIn = Carbon::parse($this->check_in_at);
        $checkOut = Carbon::parse($this->check_out_at);

        // Selisih menit SELALU positif
        $minutes = $checkIn->diffInMinutes($checkOut);

        return round($minutes / 60, 2);
    }


    /**
     * Update status absensi berdasarkan data yang ada
     */
    public function updateStatus(): void
    {
        if (!$this->check_in_at) {
            $this->status = 'absent';
        }
        elseif (!$this->check_out_at) {
            $this->status = 'incomplete';
        }
        elseif ($this->late_minutes > 0) {
            $this->status = 'late';
        }
        else {
            $this->status = 'present';
        }

        $this->save();
    }
}
<?php
// app/Services/AttendanceService.php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Proses tap RFID - menentukan apakah ini check-in atau check-out
     */
    public function processTap(Employee $employee, $tappedAt): array
    {
        $tappedTime = Carbon::parse($tappedAt);

        $workSchedule = $employee->department->activeWorkSchedule;
        if (!$workSchedule) {
            throw new \Exception('Departemen tidak memiliki jadwal kerja aktif');
        }

        /**
         * 1️⃣ Cari attendance yang masih terbuka (belum checkout)
         */
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->orderBy('check_in_at', 'desc')
            ->first();

        /**
         * 2️⃣ Jika ADA → CHECK-OUT
         */
        if ($attendance) {
            $attendance->check_out_at = $tappedTime;
            $attendance->work_hours = $attendance->calculateWorkHours();
            $attendance->updateStatus();

            return [
                'attendance' => $attendance->fresh(),
                'message' => 'Check-out berhasil',
            ];
        }

        /**
         * 3️⃣ Jika TIDAK ADA → CHECK-IN
         */
        $workDate = $workSchedule->determineWorkDate($tappedTime);

        // 🔎 CEK apakah sudah ada attendance di tanggal kerja ini
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('work_date', $workDate)
            ->first();

        if ($existingAttendance) {
            return [
                'attendance' => $existingAttendance,
                'message' => 'Absensi hari ini sudah lengkap',
            ];
        }

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => $workDate,
            'check_in_at' => $tappedTime,
            'late_minutes' => $workSchedule->calculateLateMinutes($tappedTime),
            'work_hours' => 0,
            'status' => 'incomplete',
        ]);

        return [
            'attendance' => $attendance->fresh(),
            'message' => 'Check-in berhasil',
        ];
    }

    /**
     * Helper to calculate Haversine distance in meters
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; // in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Proses tap Mobile Web dengan Foto dan Lokasi
     */
    public function processMobileTap(Employee $employee, $tappedAt, $photoPath, $lat, $lng): array
    {
        $setting = \App\Models\Setting::first();
        if ($setting && $setting->latitude && $setting->longitude && $setting->radius) {
            $distance = $this->calculateDistance($setting->latitude, $setting->longitude, $lat, $lng);
            if ($distance > $setting->radius) {
                throw new \Exception('Lokasi Anda (' . round($distance) . 'm) berada di luar jangkauan absensi yang diizinkan (' . $setting->radius . 'm).');
            }
        }

        $tappedTime = Carbon::parse($tappedAt);

        $workSchedule = $employee->department->activeWorkSchedule;
        if (!$workSchedule) {
            throw new \Exception('Departemen tidak memiliki jadwal kerja aktif');
        }

        /**
         * 1️⃣ Cari attendance yang masih terbuka (belum checkout)
         */
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->orderBy('check_in_at', 'desc')
            ->first();

        /**
         * 2️⃣ Jika ADA → CHECK-OUT
         */
        if ($attendance) {
            $attendance->check_out_at = $tappedTime;
            $attendance->photo_out_path = $photoPath;
            $attendance->location_out_lat = $lat;
            $attendance->location_out_lng = $lng;
            $attendance->work_hours = $attendance->calculateWorkHours();
            $attendance->updateStatus();

            return [
                'attendance' => $attendance->fresh(),
                'message' => 'Check-out berhasil',
            ];
        }

        /**
         * 3️⃣ Jika TIDAK ADA → CHECK-IN
         */
        $workDate = $workSchedule->determineWorkDate($tappedTime);

        // 🔎 CEK apakah sudah ada attendance di tanggal kerja ini
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('work_date', $workDate)
            ->first();

        if ($existingAttendance) {
            return [
                'attendance' => $existingAttendance,
                'message' => 'Absensi hari ini sudah lengkap',
            ];
        }

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => $workDate,
            'check_in_at' => $tappedTime,
            'photo_in_path' => $photoPath,
            'location_in_lat' => $lat,
            'location_in_lng' => $lng,
            'late_minutes' => $workSchedule->calculateLateMinutes($tappedTime),
            'work_hours' => 0,
            'status' => 'incomplete',
        ]);

        return [
            'attendance' => $attendance->fresh(),
            'message' => 'Check-in berhasil',
        ];
    }

    /**
     * Update manual attendance record
     */
    public function updateAttendance(Attendance $attendance, array $data): Attendance
    {
        if (isset($data['check_in_at'])) {
            $attendance->check_in_at = $data['check_in_at'];

            $workSchedule = $attendance->employee->department->activeWorkSchedule;
            if ($workSchedule) {
                $attendance->late_minutes = $workSchedule->calculateLateMinutes(
                    Carbon::parse($data['check_in_at'])
                );
            }
        }

        if (isset($data['check_out_at'])) {
            $attendance->check_out_at = $data['check_out_at'];
        }

        if (isset($data['notes'])) {
            $attendance->notes = $data['notes'];
        }

        // Recalculate work hours and status
        $attendance->work_hours = $attendance->calculateWorkHours();
        $attendance->updateStatus();
        $attendance->save();

        return $attendance;
    }

    /**
     * Generate attendance summary untuk periode tertentu
     * Biasanya dipanggil di akhir bulan sebelum proses payroll
     */
    public function generateMonthlySummary(Employee $employee, int $year, int $month): void
    {
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->get();

        $summary = $employee->attendanceSummaries()->updateOrCreate(
        [
            'year' => $year,
            'month' => $month,
        ],
        [
            'total_present' => $attendances->whereIn('status', ['present', 'late'])->count(),
            'total_late' => $attendances->where('status', 'late')->count(),
            'total_absent' => $attendances->where('status', 'absent')->count(),
            'total_work_hours' => $attendances->sum('work_hours'),
            'total_late_minutes' => $attendances->sum('late_minutes'),
        ]
        );
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Models\Employee;
use App\Services\AttendanceService;
use App\Models\Attendance;
use Carbon\Carbon;

class MobileAttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService
        )
    {
    }

    /**
     * Tampilkan halaman tap mobile
     */
    public function mobileTapView()
    {
        $employeeId = session('employee_id');
        $employee = Employee::find($employeeId);

        if (!$employee || !$employee->is_active) {
            return redirect()->route('mobile.login')->with('error', 'Akun tidak valid atau tidak aktif.');
        }

        $setting = \App\Models\Setting::first();

        // Cek absensi hari ini (beradasarkan tanggal saat ini)
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('work_date', Carbon::today())
            ->first();

        $attendanceStatus = 'none'; // Belum absen masuk
        if ($todayAttendance) {
            if ($todayAttendance->check_in_at && !$todayAttendance->check_out_at) {
                $attendanceStatus = 'in'; // Sudah absen masuk, belum absen pulang
            }
            elseif ($todayAttendance->check_in_at && $todayAttendance->check_out_at) {
                $attendanceStatus = 'done'; // Sudah absen masuk dan pulang
            }
        }

        return view('mobile.tap', compact('employee', 'setting', 'attendanceStatus'));
    }

    /**
     * Proses API Tap dari device mobile
     */
    public function mobileTap(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $employeeId = session('employee_id');
        $employee = Employee::find($employeeId);

        if (!$employee || !$employee->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak valid. Silakan login kembali.',
            ], 401);
        }

        $filename = null;

        try {
            $result = $this->attendanceService->processMobileTap(
                $employee,
                now(),
                $filename,
                $request->latitude,
                $request->longitude
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['attendance'],
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
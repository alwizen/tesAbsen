<?php
// app/Http/Controllers/AttendanceController.php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService
    ) {}

    /**
     * Proses tap RFID
     * Endpoint ini dipanggil oleh RFID reader
     */
    public function tap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rfid_number' => 'required|string',
            'tapped_at' => 'nullable|date', // Opsional, default now()
        ]);

        $employee = Employee::where('rfid_number', $validated['rfid_number'])
            ->where('is_active', true)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'RFID tidak terdaftar atau karyawan tidak aktif',
            ], 404);
        }

        $result = $this->attendanceService->processTap(
            $employee,
            $validated['tapped_at'] ?? now()
        );

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $result['attendance'],
        ]);
    }

    /**
     * Get attendance by date range
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $query = Attendance::with(['employee.department'])
            ->whereBetween('work_date', [$validated['start_date'], $validated['end_date']]);

        if (isset($validated['employee_id'])) {
            $query->where('employee_id', $validated['employee_id']);
        }

        $attendances = $query->orderBy('work_date', 'desc')
            ->orderBy('check_in_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attendances,
        ]);
    }

    /**
     * Manual correction untuk absensi
     */
    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $validated = $request->validate([
            'check_in_at' => 'nullable|date',
            'check_out_at' => 'nullable|date|after:check_in_at',
            'notes' => 'nullable|string',
        ]);

        $this->attendanceService->updateAttendance($attendance, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil diupdate',
            'data' => $attendance->fresh(),
        ]);
    }
}

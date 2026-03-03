<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Models\Employee;
use App\Services\AttendanceService;
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

        return view('mobile.tap', compact('employee', 'setting'));
    }

    /**
     * Proses API Tap dari device mobile
     */
    public function mobileTap(Request $request): JsonResponse
    {
        $request->validate([
            'image_token' => 'required|string', // Base64 image
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

        // Simpan foto base64
        $imageData = $request->image_token;
        $imageParts = explode(";base64,", $imageData);
        if (count($imageParts) == 2) {
            $imageTypeAux = explode("image/", $imageParts[0]);
            $imageType = $imageTypeAux[1];
            $imageBase64 = base64_decode($imageParts[1]);
            $filename = 'attendances/' . $employee->id . '_' . time() . '.' . $imageType;

            Storage::disk('public')->put($filename, $imageBase64);
        }
        else {
            return response()->json([
                'success' => false,
                'message' => 'Format gambar tidak valid',
            ], 400);
        }

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
<?php
// app/Http/Controllers/PayrollController.php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Services\PayrollService;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PayrollController extends Controller
{
    public function __construct(
        private PayrollService $payrollService,
        private AttendanceService $attendanceService
    ) {}

    /**
     * Generate payroll untuk periode tertentu
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2050',
            'month' => 'required|integer|min:1|max:12',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        // Generate attendance summaries terlebih dahulu
        $employees = \App\Models\Employee::where('is_active', true)
            ->when(isset($validated['employee_ids']), function ($q) use ($validated) {
                $q->whereIn('id', $validated['employee_ids']);
            })
            ->get();

        foreach ($employees as $employee) {
            $this->attendanceService->generateMonthlySummary(
                $employee,
                $validated['year'],
                $validated['month']
            );
        }

        // Generate payroll
        $payroll = $this->payrollService->generatePayroll(
            $validated['year'],
            $validated['month'],
            $validated['employee_ids'] ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Payroll berhasil digenerate',
            'data' => $payroll->load('details.employee'),
        ]);
    }

    /**
     * Get payroll by ID
     */
    public function show(Payroll $payroll): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $payroll->load(['details.employee.department', 'details.attendanceSummary']),
        ]);
    }

    /**
     * Process payroll
     */
    public function process(Request $request, Payroll $payroll): JsonResponse
    {
        // Dalam implementasi nyata, ambil employee dari auth user
        $processedBy = \App\Models\Employee::first(); // Dummy

        $this->payrollService->processPayroll($payroll, $processedBy);

        return response()->json([
            'success' => true,
            'message' => 'Payroll berhasil diproses',
            'data' => $payroll->fresh(),
        ]);
    }

    /**
     * Mark payroll as paid
     */
    public function markAsPaid(Payroll $payroll): JsonResponse
    {
        $this->payrollService->markAsPaid($payroll);

        return response()->json([
            'success' => true,
            'message' => 'Payroll ditandai sebagai sudah dibayar',
            'data' => $payroll->fresh(),
        ]);
    }

    /**
     * List all payrolls
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'nullable|integer',
            'status' => 'nullable|in:draft,processed,paid,cancelled',
        ]);

        $query = Payroll::with('details');

        if (isset($validated['year'])) {
            $query->where('year', $validated['year']);
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $payrolls = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payrolls,
        ]);
    }
}

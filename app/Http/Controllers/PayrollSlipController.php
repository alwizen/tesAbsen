<?php

namespace App\Http\Controllers;

use App\Models\PayrollDetail;
use Illuminate\Http\Request;

class PayrollSlipController extends Controller
{
    public function show(PayrollDetail $payrollDetail)
    {
        $payrollDetail->load(['employee', 'payroll', 'employee.department']);

        $employee   = $payrollDetail->employee;
        $department = $employee->department;
        $payroll    = $payrollDetail->payroll;

        return view('payroll.slip', [
            'detail'       => $payrollDetail,
            'employee'     => $employee,
            'department'   => $department,
            'payroll'      => $payroll,
            'app_address'     => config('app.address'),
            'app_city'        => config('app.city'),
            'accountantName'  => config('app.accountant_name'),
            'tanggal_cetak'  => now()->translatedFormat('d F Y'),
        ]);
    }
}

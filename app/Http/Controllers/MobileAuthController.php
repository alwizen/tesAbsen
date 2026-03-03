<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class MobileAuthController extends Controller
{
    /**
     * Tampilkan halaman login mobile
     */
    public function loginView()
    {
        return view('mobile.login');
    }

    /**
     * Proses login berdasarkan nomor HP
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $employee = Employee::where('phone', $request->phone)
            ->where('is_active', true)
            ->first();

        if (!$employee) {
            return back()->with('error', 'Nomor HP tidak ditemukan atau akun tidak aktif.');
        }

        // Simpan id employee ke session
        session(['employee_id' => $employee->id]);

        return redirect()->route('mobile.tap');
    }

    /**
     * Proses logout mobile
     */
    public function logout(Request $request)
    {
        $request->session()->forget('employee_id');
        return redirect()->route('mobile.login');
    }
}
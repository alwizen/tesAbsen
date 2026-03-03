<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PayrollController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('attendance')->group(function () {
    Route::post('tap', [AttendanceController::class, 'tap']); // Endpoint untuk RFID tap
    Route::get('/', [AttendanceController::class, 'index']);
    Route::put('{attendance}', [AttendanceController::class, 'update']);
});

// Payroll Routes
Route::prefix('payroll')->group(function () {
    Route::get('/', [PayrollController::class, 'index']);
    Route::post('generate', [PayrollController::class, 'generate']);
    Route::get('{payroll}', [PayrollController::class, 'show']);
    Route::post('{payroll}/process', [PayrollController::class, 'process']);
    Route::post('{payroll}/mark-as-paid', [PayrollController::class, 'markAsPaid']);
});

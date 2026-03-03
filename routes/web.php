<?php

use App\Http\Controllers\PayrollSlipController;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MobileAuthController;
use App\Http\Controllers\MobileAttendanceController;

Route::view('/tap', 'attendance.tap');

// Mobile Web Routes
Route::get('/mobile/login', [MobileAuthController::class , 'loginView'])->name('mobile.login');
Route::post('/mobile/login', [MobileAuthController::class , 'login']);
Route::post('/mobile/logout', [MobileAuthController::class , 'logout'])->name('mobile.logout');

Route::middleware([\App\Http\Middleware\CheckEmployeeSession::class])->group(function () {
    Route::get('/mobile/tap', [MobileAttendanceController::class , 'mobileTapView'])->name('mobile.tap');
    Route::post('/api/attendance/mobile-tap', [MobileAttendanceController::class , 'mobileTap']);
});

Route::get('/payroll/slip/{payrollDetail}', [PayrollSlipController::class , 'show'])
    ->name('payroll.slip')
    ->middleware(['auth']);

// Route::get('/debug-work-date', function () {
//     $schedule = WorkSchedule::where('is_active', true)->first();

//     if (!$schedule) {
//         return response()->json(['error' => 'Tidak ada schedule aktif']);
//     }

//     echo "<h2>Testing determineWorkDate()</h2>";
//     echo "<p><strong>Schedule:</strong> {$schedule->check_in_time} - {$schedule->check_out_time}</p>";
//     echo "<p><strong>Overnight:</strong> " . ($schedule->is_overnight ? 'YES' : 'NO') . "</p>";

//     // Hitung midpoint
//     $checkIn = Carbon::parse($schedule->check_in_time);
//     $checkOut = Carbon::parse($schedule->check_out_time);
//     $checkInMinutes = ($checkIn->hour * 60) + $checkIn->minute;
//     $checkOutMinutes = ($checkOut->hour * 60) + $checkOut->minute;
//     $checkInNextDay = $checkInMinutes + 1440;
//     $midpoint = ($checkOutMinutes + $checkInNextDay) / 2;
//     if ($midpoint >= 1440) $midpoint -= 1440;
//     $midpointHour = floor($midpoint / 60);
//     $midpointMinute = $midpoint % 60;

//     echo "<p><strong>Midpoint (batas shift):</strong> " . sprintf("%02d:%02d", $midpointHour, $midpointMinute) . "</p>";
//     echo "<p style='color: #666; font-size: 14px;'>Tap sebelum midpoint = shift kemarin | Tap setelah midpoint = shift baru</p>";
//     echo "<hr>";

//     // Test cases untuk shift 19:00-03:00
//     // Midpoint = 23:00 (batas antara shift lama dan baru)
//     $testCases = [
//         // [Date, Time, Expected Work Date, Description]
//         ['2026-01-08', '03:00:00', '2026-01-07', 'Checkout shift Jan 7→8 (jam check-out)'],
//         ['2026-01-08', '10:00:00', '2026-01-07', 'Setelah checkout, sebelum midpoint → shift kemarin'],
//         ['2026-01-08', '16:00:00', '2026-01-07', 'Idle time, sebelum check-in → masih dianggap shift kemarin'],
//         ['2026-01-08', '18:00:00', '2026-01-07', '1 jam sebelum check-in → masih shift kemarin'],
//         ['2026-01-08', '19:00:00', '2026-01-08', 'Tepat check-in → shift Jan 8→9 dimulai ✅'],
//         ['2026-01-08', '20:00:00', '2026-01-08', 'Check-in shift Jan 8→9'],
//         ['2026-01-08', '23:00:00', '2026-01-08', 'Check-in shift Jan 8→9'],
//         ['2026-01-08', '23:40:00', '2026-01-08', '🔥 Check-in shift Jan 8→9 (actual case)'],
//         ['2026-01-09', '00:00:00', '2026-01-08', 'Masih shift Jan 8→9 (lewat tengah malam)'],
//         ['2026-01-09', '01:00:00', '2026-01-08', 'Masih shift Jan 8→9'],
//         ['2026-01-09', '02:00:00', '2026-01-08', 'Masih shift Jan 8→9'],
//         ['2026-01-09', '03:00:00', '2026-01-08', 'Checkout shift Jan 8→9 (tepat waktu)'],
//         ['2026-01-09', '05:00:00', '2026-01-08', 'Checkout terlambat shift Jan 8→9'],
//         ['2026-01-09', '07:00:00', '2026-01-08', 'Checkout sangat terlambat shift Jan 8→9'],
//         ['2026-01-09', '10:00:00', '2026-01-08', 'Setelah checkout, sebelum midpoint'],
//         ['2026-01-09', '15:59:00', '2026-01-08', 'Masih sebelum midpoint'],
//         ['2026-01-09', '16:00:00', '2026-01-08', 'Masih sebelum midpoint → shift kemarin'],
//         ['2026-01-09', '18:00:00', '2026-01-08', '1 jam sebelum check-in → shift kemarin'],
//         ['2026-01-09', '19:00:00', '2026-01-09', '🔥 Tepat check-in → shift Jan 9→10 dimulai ✅'],
//         ['2026-01-09', '20:00:00', '2026-01-09', 'Check-in shift Jan 9→10'],
//         ['2026-01-09', '23:00:00', '2026-01-09', 'Check-in shift Jan 9→10'],
//         ['2026-01-09', '23:40:00', '2026-01-09', 'Check-in shift Jan 9→10'],
//         ['2026-01-10', '00:00:00', '2026-01-09', 'Masih shift Jan 9→10'],
//         ['2026-01-10', '03:00:00', '2026-01-09', 'Checkout shift Jan 9→10'],
//         ['2026-01-10', '07:40:00', '2026-01-09', 'Checkout terlambat shift Jan 9→10'],
//     ];

//     echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
//     echo "<tr style='background: #f0f0f0;'>
//             <th>Status</th>
//             <th>Tap Date</th>
//             <th>Tap Time</th>
//             <th>work_date (Result)</th>
//             <th>Expected</th>
//             <th>Description</th>
//           </tr>";

//     $correctCount = 0;
//     $totalCount = count($testCases);

//     foreach ($testCases as $case) {
//         list($date, $time, $expectedWorkDate, $description) = $case;

//         $tapTime = Carbon::parse("$date $time");
//         $workDate = $schedule->determineWorkDate($tapTime);
//         $resultWorkDate = $workDate->format('Y-m-d');

//         $isCorrect = $resultWorkDate === $expectedWorkDate;
//         if ($isCorrect) $correctCount++;

//         $status = $isCorrect ? '✅' : '❌';
//         $rowColor = $isCorrect ? '#d4edda' : '#f8d7da';
//         $highlight = strpos($description, '🔥') !== false ? 'font-weight: bold; font-size: 14px;' : '';

//         echo "<tr style='background: $rowColor; $highlight'>";
//         echo "<td style='text-align: center; font-size: 20px;'>$status</td>";
//         echo "<td>$date</td>";
//         echo "<td><strong>$time</strong></td>";
//         echo "<td><strong style='color: " . ($isCorrect ? 'green' : 'red') . ";'>$resultWorkDate</strong></td>";
//         echo "<td>$expectedWorkDate</td>";
//         echo "<td>$description</td>";
//         echo "</tr>";
//     }

//     echo "</table>";
//     echo "<hr>";
//     echo "<h3>Summary:</h3>";
//     echo "<p><strong>$correctCount / $totalCount</strong> test cases passed</p>";
//     echo "<p>✅ = Logic BENAR | ❌ = Logic SALAH</p>";

//     if ($correctCount === $totalCount) {
//         echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 ALL TESTS PASSED! Logic sudah benar.</p>";
//     } else {
//         echo "<p style='color: red; font-size: 18px; font-weight: bold;'>⚠️ Ada bug! Perlu perbaikan logic.</p>";
//     }
// });
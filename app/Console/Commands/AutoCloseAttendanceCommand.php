<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AutoCloseAttendanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-close-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically close attendances that have been open for more than 18 hours.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for incomplete attendances older than 18 hours...');

        $attendances = Attendance::with(['employee.department.activeWorkSchedule'])
            ->whereNull('check_out_at')
            ->whereNotNull('check_in_at')
            ->where('check_in_at', '<', now()->subHours(18))
            ->get();

        $count = 0;
        foreach ($attendances as $attendance) {
            $checkIn = Carbon::parse($attendance->check_in_at);
            
            // Waktu default check-out: 8 jam dari check-in (jika tidak ada jadwal)
            $checkOutTime = $checkIn->copy()->addHours(8);

            $schedule = $attendance->employee?->department?->activeWorkSchedule ?? null;
            if (!$schedule) {
                // coba ambil jadwal apa pun dari departemen jika activeWorkSchedule tidak ketemu
                $schedule = $attendance->employee?->department?->workSchedules()->first();
            }

            if ($schedule && $schedule->check_out_time) {
                $scheduledOut = Carbon::parse($schedule->check_out_time);
                $checkOutDate = \Carbon\Carbon::parse($attendance->work_date ?? $checkIn);
                
                if ($schedule->is_overnight) {
                    $scheduledIn = Carbon::parse($schedule->check_in_time);
                    if ($scheduledOut->hour < $scheduledIn->hour) {
                         $checkOutDate->addDay();
                    }
                }
                
                $checkOutTime = $checkOutDate->setTime(
                    $scheduledOut->hour, 
                    $scheduledOut->minute, 
                    $scheduledOut->second
                );

                // Pastikan check out tidak lebih kecil dari check in (misal karena beda tanggal kacau)
                if ($checkOutTime->lte($checkIn)) {
                    $checkOutTime = $checkIn->copy()->addHours(8);
                }
            }

            // Simpan check out dan panggil updateStatus (didalamnya udah di save)
            $attendance->check_out_at = $checkOutTime;
            $attendance->work_hours = $attendance->calculateWorkHours();
            
            // Catatan sistem ditambahkan
            $notes = $attendance->notes ? trim($attendance->notes) . "\n" : "";
            $attendance->notes = $notes . "[Sistem] Check-out otomatis karena >18 jam.";
            
            $attendance->updateStatus();
            
            $count++;
        }

        $this->info("Closed $count attendances.");
    }
}

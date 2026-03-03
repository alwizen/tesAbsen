<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendanceStat extends StatsOverviewWidget
{
    protected static bool $isLazy = false;
    
    protected function getStats(): array
    {
        $today = Carbon::today();

        $totalEmployees = Employee::where('is_active', true)->count();

        $presentToday = Attendance::whereDate('work_date', $today)
            ->whereNotNull('check_in_at')
            ->count();

        $absentToday = Attendance::whereDate('work_date', $today)
            ->where('status', 'absent')
            ->count();

        return [
            Stat::make('Total Relawan', $totalEmployees)
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Masuk Hari Ini', $presentToday)
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Tidak Masuk Hari Ini', $absentToday)
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}

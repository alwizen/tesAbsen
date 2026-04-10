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

    protected function getColumns(): int
    {
        return 2;
    }

    protected function getStats(): array
    {
        $today = Carbon::today();

        $totalEmployees = Employee::where('is_active', true)->count();

        $presentToday = Attendance::whereDate('work_date', $today)
            ->whereNotNull('check_in_at')
            ->count();

        return [
            Stat::make('Total Relawan', $totalEmployees)
                ->icon('heroicon-o-users')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Masuk Hari Ini', $presentToday)
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->chart([2, 5, 3, 10, 15, 12, 17]),
        ];
    }
}

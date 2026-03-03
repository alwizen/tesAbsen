<?php

namespace App\Filament\Resources\PayrollDetails\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PayrollDetailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('payroll.id')
                    ->label('Payroll'),
                TextEntry::make('employee.name')
                    ->label('Employee'),
                TextEntry::make('attendanceSummary.id')
                    ->label('Attendance summary')
                    ->placeholder('-'),
                TextEntry::make('salary_type')
                    ->placeholder('-'),
                TextEntry::make('daily_rate')
                    ->numeric(),
                TextEntry::make('hourly_rate')
                    ->numeric(),
                TextEntry::make('allowances')
                    ->numeric(),
                TextEntry::make('pj_allowance')
                    ->numeric(),
                TextEntry::make('total_work_days')
                    ->numeric(),
                TextEntry::make('total_work_hours')
                    ->numeric(),
                TextEntry::make('total_late_minutes')
                    ->numeric(),
                TextEntry::make('base_salary')
                    ->numeric(),
                TextEntry::make('late_deduction')
                    ->numeric(),
                TextEntry::make('other_deductions')
                    ->numeric(),
                TextEntry::make('bonuses')
                    ->numeric(),
                TextEntry::make('net_salary')
                    ->numeric(),
                TextEntry::make('calculation_notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

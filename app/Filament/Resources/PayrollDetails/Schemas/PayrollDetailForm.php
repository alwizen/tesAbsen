<?php

namespace App\Filament\Resources\PayrollDetails\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayrollDetailForm
{
    private static function recalculateNetSalary(callable $set, callable $get): void
    {
        $base = (float) ($get('base_salary') ?? 0);
        $allowances = (float) ($get('allowances') ?? 0);
        $bonuses = (float) ($get('bonuses') ?? 0);
        $late = (float) ($get('late_deduction') ?? 0);
        $other = (float) ($get('other_deductions') ?? 0);

        $total = $base + $allowances + $bonuses - $late - $other;

        $set('net_salary', max(0, round($total)));
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informasi Relawan')
                    ->columnSpan(2)
                    ->columns(2)
                    ->components([
                        Select::make('payroll_id')
                            ->label('Periode Penggajian')
                            ->relationship(
                                name: 'payroll',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn($query) =>
                                $query->where('status', 'draft')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                "Payroll {$record->year} - " .
                                    \Carbon\Carbon::create()
                                    ->month($record->month)
                                    ->translatedFormat('F') .
                                    " ({$record->period_start->format('d M')} - {$record->period_end->format('d M')})"
                            )
                            ->required()
                            ->afterStateUpdated(fn($state, $set) => $set('employee_id', null))
                            ->live(onBlur: true),

                        Select::make('employee_id')
                            ->label('Relawan')
                            ->required()
                            ->live(onBlur: true)
                            ->getOptionLabelFromRecordUsing(
                                fn($record) => "{$record->employee_number} - {$record->name}"
                            )
                            ->relationship(
                                name: 'employee',
                                titleAttribute: 'name',
                                modifyQueryUsing: function ($query, callable $get, $record) {
                                    $payrollId = $get('payroll_id');
                                    if (! $payrollId) return;

                                    $currentEmployeeId = $record?->employee_id;

                                    $query->whereNotIn('id', function ($sub) use ($payrollId, $currentEmployeeId) {
                                        $sub->select('employee_id')
                                            ->from('payroll_details')
                                            ->where('payroll_id', $payrollId)
                                            ->when(
                                                $currentEmployeeId,
                                                fn($q) => $q->where('employee_id', '!=', $currentEmployeeId)
                                            );
                                    });
                                }
                            )
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (! $state) return;

                                $payrollId = $get('payroll_id');
                                if (! $payrollId) return;

                                // =====================
                                // AMBIL EMPLOYEE + DEPARTMENT
                                // =====================
                                $employee = \App\Models\Employee::with('department')->find($state);
                                if (! $employee || ! $employee->department) return;

                                $department = $employee->department;

                                // =====================
                                // SET TARIF DARI DEPARTMENT
                                // =====================
                                $set('salary_type', $department->salary_type);
                                $set('daily_rate', $department->daily_rate);
                                $set('hourly_rate', $department->hourly_rate);
                                $set('allowances', $department->allowance);

                                if (isset($employee->is_pj) && ! $employee->is_pj) {
                                    $set('pj_allowance', 0);
                                } else {
                                    $set('pj_allowance', $department->pj_allowance);
                                }

                                // =====================
                                // ATTENDANCE SUMMARY
                                // =====================
                                $totalWorkDays  = 0;
                                $totalWorkHours = 0;
                                $totalLateMinutes = 0;

                                $attendanceSummary = \App\Models\AttendanceSummary::where('employee_id', $state)
                                    ->where('payroll_id', $payrollId)
                                    ->first();

                                if ($attendanceSummary) {
                                    $set('attendance_summary_id', $attendanceSummary->id);
                                    $totalWorkDays    = $attendanceSummary->total_work_days ?? 0;
                                    $totalWorkHours   = $attendanceSummary->total_work_hours ?? 0;
                                    $totalLateMinutes = $attendanceSummary->total_late_minutes ?? 0;

                                    $set('total_work_days', $totalWorkDays);
                                    $set('total_work_hours', $totalWorkHours);
                                    $set('total_late_minutes', $totalLateMinutes);
                                } else {
                                    $payroll = \App\Models\Payroll::find($payrollId);
                                    if ($payroll) {
                                        $attendances = \App\Models\Attendance::where('employee_id', $state)
                                            ->whereBetween('work_date', [$payroll->period_start, $payroll->period_end])
                                            ->whereIn('status', ['present', 'late', 'incomplete'])
                                            ->get();

                                        $totalWorkDays = $attendances->count();

                                        foreach ($attendances as $attendance) {
                                            if ($attendance->check_in_at && $attendance->check_out_at) {
                                                $checkIn  = \Carbon\Carbon::parse($attendance->check_in_at);
                                                $checkOut = \Carbon\Carbon::parse($attendance->check_out_at);
                                                $totalWorkHours += $checkIn->diffInMinutes($checkOut) / 60;
                                            }
                                            $totalLateMinutes += $attendance->late_minutes ?? 0;
                                        }

                                        $totalWorkHours = round($totalWorkHours, 2);

                                        $set('total_work_days', $totalWorkDays);
                                        $set('total_work_hours', $totalWorkHours);
                                        $set('total_late_minutes', $totalLateMinutes);
                                    }
                                }

                                // =====================
                                // HITUNG BASE SALARY OTOMATIS
                                // =====================
                                $salaryType = $department->salary_type;

                                if ($salaryType === 'daily') {
                                    $baseSalary = ($department->daily_rate ?? 0) * $totalWorkDays;
                                } elseif ($salaryType === 'hourly') {
                                    $baseSalary = ($department->hourly_rate ?? 0) * $totalWorkHours;
                                } else {
                                    $baseSalary = 0;
                                }

                                $set('base_salary', round($baseSalary));
                                self::recalculateNetSalary($set, $get);
                            }),
                    ]),

                Section::make('Data Absensi')
                    ->columnSpan(2)
                    ->columns(2)
                    ->components([
                        TextInput::make('total_work_days')
                            ->label('Total Hari Kerja')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('hari'),

                        TextInput::make('total_work_hours')
                            ->label('Total Jam Kerja')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('jam')
                            ->readOnly()
                            ->dehydrated(), // Tetap disimpan meski readonly

                        // TextInput::make('total_late_minutes')
                        //     ->label('Total Terlambat')
                        //     ->numeric()
                        //     ->default(0)
                        //     ->minValue(0)
                        //     ->suffix('menit'),
                    ]),

                Section::make('Tarif')
                    ->columnSpan(3)
                    ->columns(2)
                    ->components([
                        TextInput::make('daily_rate')
                            ->label('Tarif per Hari')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->step(1000),

                        TextInput::make('hourly_rate')
                            ->label('Tarif per Jam')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->step(1000),
                    ]),

                Section::make('Komponen Gaji')
                    ->columnSpan(3)
                    ->columns(3)
                    ->components([
                        TextInput::make('base_salary')
                            ->label('Gaji Pokok')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->prefix('Rp')
                            ->step(1000)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn($state, $set, $get) =>
                                self::recalculateNetSalary($set, $get)
                            ),


                        TextInput::make('allowances')
                            ->label('Tunjangan')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->step(1000)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn($state, $set, $get) =>
                                self::recalculateNetSalary($set, $get)
                            ),


                        TextInput::make('bonuses')
                            ->label('Bonus')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->step(1000)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn($state, $set, $get) =>
                                self::recalculateNetSalary($set, $get)
                            ),

                    ]),

                Section::make('Potongan')
                    ->columnSpan(3)
                    ->columns(2)
                    ->components([
                        TextInput::make('late_deduction')
                            ->label('Potongan Terlambat')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->step(1000)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn($state, $set, $get) =>
                                self::recalculateNetSalary($set, $get)
                            ),


                        TextInput::make('other_deductions')
                            ->label('Potongan Lainnya')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->step(1000)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn($state, $set, $get) =>
                                self::recalculateNetSalary($set, $get)
                            ),

                    ]),

                Section::make('Total')
                    ->columnSpan(3)
                    ->columns(2)
                    ->components([
                        TextInput::make('net_salary')
                            ->label('Gaji Bersih')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->prefix('Rp')
                            ->readOnly(),

                        Textarea::make('calculation_notes')
                            ->label('Catatan Perhitungan')
                            ->rows(3)
                            ->placeholder('Catatan atau detail perhitungan'),
                    ]),
            ]);
    }
}

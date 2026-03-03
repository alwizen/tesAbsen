<?php

namespace App\Filament\Resources\PayrollDetails;

use App\Filament\Resources\PayrollDetails\Pages\ManagePayrollDetails;
use App\Models\PayrollDetail;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use UnitEnum;

class PayrollDetailResource extends Resource
{
    protected static ?string $model = PayrollDetail::class;

    protected static ?string $navigationLabel = 'Penggajian (Maintenance)';

    protected static string | UnitEnum | null $navigationGroup = 'Gaji Relawan';

    protected static ?string $label = 'Penggajian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informasi Karyawan')
                    ->columnSpan(2)
                    ->columns(2)
                    ->components([
                        Select::make('payroll_id')
                            ->label('Periode Payroll')
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
                            ->required(),


                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(
                                fn($record) => "{$record->employee_number} - {$record->name}"
                            )
                            ->reactive()
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

                                // Kalau nanti kamu punya flag PJ di employee
                                if (isset($employee->is_pj) && ! $employee->is_pj) {
                                    $set('pj_allowance', 0);
                                } else {
                                    $set('pj_allowance', $department->pj_allowance);
                                }

                                // =====================
                                // ATTENDANCE SUMMARY
                                // =====================
                                $attendanceSummary = \App\Models\AttendanceSummary::where('employee_id', $state)
                                    ->where('payroll_id', $payrollId)
                                    ->first();

                                if ($attendanceSummary) {
                                    $set('attendance_summary_id', $attendanceSummary->id);
                                    $set('total_work_days', $attendanceSummary->total_work_days ?? 0);
                                    $set('total_work_hours', $attendanceSummary->total_work_hours ?? 0);
                                    $set('total_late_minutes', $attendanceSummary->total_late_minutes ?? 0);
                                } else {
                                    $payroll = \App\Models\Payroll::find($payrollId);
                                    if ($payroll) {
                                        $attendances = \App\Models\Attendance::where('employee_id', $state)
                                            ->whereBetween('work_date', [$payroll->period_start, $payroll->period_end])
                                            ->whereIn('status', ['present', 'late', 'incomplete'])
                                            ->get();

                                        $totalWorkDays = $attendances->count();
                                        $totalWorkHours = 0;
                                        $totalLateMinutes = 0;

                                        foreach ($attendances as $attendance) {
                                            if ($attendance->check_in_at && $attendance->check_out_at) {
                                                $checkIn = \Carbon\Carbon::parse($attendance->check_in_at);
                                                $checkOut = \Carbon\Carbon::parse($attendance->check_out_at);
                                                $totalWorkHours += $checkIn->diffInMinutes($checkOut) / 60;
                                            }

                                            $totalLateMinutes += $attendance->late_minutes ?? 0;
                                        }

                                        $set('total_work_days', $totalWorkDays);
                                        $set('total_work_hours', round($totalWorkHours, 2));
                                        $set('total_late_minutes', $totalLateMinutes);
                                    }
                                }
                            }),
                    ]),

                Section::make('Data Absensi')
                    ->columnSpan(3)
                    ->columns(3)
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

                        TextInput::make('total_late_minutes')
                            ->label('Total Terlambat')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('menit'),
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
                            ->step(1000),

                        TextInput::make('allowances')
                            ->label('Tunjangan')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->step(1000),

                        TextInput::make('bonuses')
                            ->label('Bonus')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->step(1000),
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
                            ->step(1000),

                        TextInput::make('other_deductions')
                            ->label('Potongan Lainnya')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->step(1000),
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
                            ->readOnly()
                            ->extraAttributes(['class' => 'font-bold text-lg']),

                        Textarea::make('calculation_notes')
                            ->label('Catatan Perhitungan')
                            ->rows(3)
                            ->placeholder('Catatan atau detail perhitungan'),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informasi Karyawan')
                    ->columns(2)
                    ->components([
                        TextEntry::make('employee.employee_number')
                            ->label('No. Karyawan'),

                        TextEntry::make('employee.name')
                            ->label('Nama Karyawan'),

                        TextEntry::make('employee.department.name')
                            ->label('Departemen'),

                        TextEntry::make('payroll.year')
                            ->label('Tahun'),

                        TextEntry::make('payroll.month')
                            ->label('Bulan')
                            ->formatStateUsing(
                                fn($state) =>
                                \Carbon\Carbon::create()->month($state)->translatedFormat('F')
                            ),
                    ]),

                Section::make('Data Absensi')
                    ->columns(3)
                    ->components([
                        TextEntry::make('total_work_days')
                            ->label('Total Hari Kerja')
                            ->suffix(' hari'),

                        TextEntry::make('total_work_hours')
                            ->label('Total Jam Kerja')
                            ->suffix(' jam')
                            ->formatStateUsing(fn($state) => number_format($state, 2)),

                        TextEntry::make('total_late_minutes')
                            ->label('Total Terlambat')
                            ->suffix(' menit'),
                    ]),

                Section::make('Rincian Gaji')
                    ->columns(2)
                    ->components([
                        TextEntry::make('base_salary')
                            ->label('Gaji Pokok')
                            ->money('IDR'),

                        TextEntry::make('allowances')
                            ->label('Tunjangan')
                            ->money('IDR'),

                        TextEntry::make('bonuses')
                            ->label('Bonus')
                            ->money('IDR'),

                        TextEntry::make('late_deduction')
                            ->label('Potongan Terlambat')
                            ->money('IDR'),

                        TextEntry::make('other_deductions')
                            ->label('Potongan Lainnya')
                            ->money('IDR'),

                        TextEntry::make('net_salary')
                            ->label('Gaji Bersih')
                            ->money('IDR')
                            ->weight('bold')
                            ->size('lg')
                            ->color('success'),
                    ]),

                Section::make('Catatan')
                    ->columnSpanFull()
                    ->components([
                        TextEntry::make('calculation_notes')
                            ->label('Catatan Perhitungan')
                            ->placeholder('Tidak ada catatan'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payroll.year')
                    ->label('Tahun')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('payroll.month')
                    ->label('Bulan')
                    ->formatStateUsing(
                        fn($state) =>
                        \Carbon\Carbon::create()->month($state)->translatedFormat('F')
                    )
                    ->sortable(),

                TextColumn::make('employee.employee_number')
                    ->label('No. Karyawan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.department.name')
                    ->label('Departemen')
                    ->toggleable(),

                TextColumn::make('total_work_days')
                    ->label('Hari Kerja')
                    ->suffix(' hari')
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('total_work_hours')
                    ->label('Jam Kerja')
                    ->formatStateUsing(fn($state) => number_format($state, 1))
                    ->suffix(' jam')
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('base_salary')
                    ->label('Gaji Pokok')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('net_salary')
                    ->label('Gaji Bersih')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payroll_id')
                    ->label('Periode')
                    ->relationship('payroll', 'id')
                    ->getOptionLabelFromRecordUsing(
                        fn($record) =>
                        "{$record->year} - " .
                            \Carbon\Carbon::create()->month($record->month)->translatedFormat('F')
                    ),

                SelectFilter::make('employee')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('payroll_id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePayrollDetails::route('/'),
        ];
    }
}

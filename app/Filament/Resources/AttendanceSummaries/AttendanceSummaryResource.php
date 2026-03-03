<?php

namespace App\Filament\Resources\AttendanceSummaries;

use App\Filament\Resources\AttendanceSummaries\Pages\ManageAttendanceSummaries;
use App\Models\AttendanceSummary;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Carbon\Carbon;

class AttendanceSummaryResource extends Resource
{
    protected static ?string $model = AttendanceSummary::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Ringkasan Absensi';

    protected static ?string $label = 'Ringkasan Absensi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Periode & Karyawan')
                    ->columnSpan(3)
                    ->columns(3)
                    ->components([
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->getOptionLabelFromRecordUsing(
                                fn($record) => "{$record->employee_number} - {$record->name}"
                            )
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::calculateAttendanceSummary($state, $get('year'), $get('month'), $set);
                            }),

                        TextInput::make('year')
                            ->label('Tahun')
                            ->required()
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(2099)
                            ->default(now()->year)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::calculateAttendanceSummary($get('employee_id'), $state, $get('month'), $set);
                            }),

                        Select::make('month')
                            ->label('Bulan')
                            ->required()
                            ->options([
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ])
                            ->default(now()->month)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::calculateAttendanceSummary($get('employee_id'), $get('year'), $state, $set);
                            }),
                    ]),

                Section::make('Status Kehadiran')
                    ->columnSpan(3)
                    ->columns(3)
                    ->description('Jumlah hari berdasarkan status kehadiran (otomatis dari data absensi)')
                    ->components([
                        TextInput::make('total_present')
                            ->label('Total Hadir')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('hari')
                            ->readOnly()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'font-semibold']),

                        TextInput::make('total_late')
                            ->label('Total Terlambat')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('hari')
                            ->readOnly()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'font-semibold']),

                        TextInput::make('total_absent')
                            ->label('Total Tidak Hadir')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('hari')
                            ->readOnly()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'font-semibold']),
                    ]),

                Section::make('Jam Kerja & Keterlambatan')
                    ->columnSpan(3)
                    ->columns(2)
                    ->description('Total akumulasi jam kerja dan menit terlambat (otomatis dari data absensi)')
                    ->components([
                        TextInput::make('total_work_hours')
                            ->label('Total Jam Kerja')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('jam')
                            ->readOnly()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'font-semibold'])
                            ->helperText('Total jam kerja efektif dalam sebulan'),

                        TextInput::make('total_late_minutes')
                            ->label('Total Menit Terlambat')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->suffix('menit')
                            ->readOnly()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'font-semibold'])
                            ->helperText('Akumulasi total keterlambatan dalam menit'),
                    ]),
            ]);
    }

    /**
     * Fungsi untuk menghitung ringkasan absensi otomatis
     */
    protected static function calculateAttendanceSummary($employeeId, $year, $month, callable $set): void
    {
        if (!$employeeId || !$year || !$month) {
            return;
        }

        // Tentukan periode (awal dan akhir bulan)
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Ambil data attendance untuk karyawan dan periode tersebut
        $attendances = \App\Models\Attendance::where('employee_id', $employeeId)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get();

        // Hitung berdasarkan status
        $totalPresent = $attendances->where('status', 'present')->count();
        $totalLate = $attendances->where('status', 'late')->count();
        $totalAbsent = $attendances->where('status', 'absent')->count();

        // Hitung total jam kerja (dari semua status kecuali absent)
        $totalWorkHours = $attendances
            ->whereIn('status', ['present', 'late', 'incomplete'])
            ->sum('work_hours');

        // Hitung total keterlambatan
        $totalLateMinutes = $attendances->sum('late_minutes');

        // Set semua nilai
        $set('total_present', $totalPresent);
        $set('total_late', $totalLate);
        $set('total_absent', $totalAbsent);
        $set('total_work_hours', round($totalWorkHours, 2));
        $set('total_late_minutes', $totalLateMinutes);
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
                            ->label('Nama Karyawan')
                            ->weight('bold'),

                        TextEntry::make('employee.department.name')
                            ->label('Departemen'),

                        TextEntry::make('employee.is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn($state) => $state ? 'Aktif' : 'Nonaktif')
                            ->color(fn($state) => $state ? 'success' : 'danger'),
                    ]),

                Section::make('Periode Absensi')
                    ->columns(3)
                    ->components([
                        TextEntry::make('year')
                            ->label('Tahun')
                            ->numeric(),

                        TextEntry::make('month')
                            ->label('Bulan')
                            ->formatStateUsing(
                                fn($state) =>
                                Carbon::create()->month($state)->translatedFormat('F')
                            ),

                        TextEntry::make('period')
                            ->label('Periode Lengkap')
                            ->state(
                                fn($record) =>
                                Carbon::create($record->year, $record->month)->translatedFormat('F Y')
                            )
                            ->badge()
                            ->color('info'),
                    ]),

                Section::make('Statistik Kehadiran')
                    ->columns(3)
                    ->components([
                        TextEntry::make('total_present')
                            ->label('Total Hadir')
                            ->suffix(' hari')
                            ->badge()
                            ->color('success')
                            ->icon(Heroicon::OutlinedCheckCircle),

                        TextEntry::make('total_late')
                            ->label('Total Terlambat')
                            ->suffix(' hari')
                            ->badge()
                            ->color('warning')
                            ->icon(Heroicon::OutlinedClock),

                        TextEntry::make('total_absent')
                            ->label('Total Tidak Hadir')
                            ->suffix(' hari')
                            ->badge()
                            ->color('danger')
                            ->icon(Heroicon::OutlinedXCircle),
                    ]),

                Section::make('Jam Kerja & Keterlambatan')
                    ->columns(2)
                    ->components([
                        TextEntry::make('total_work_hours')
                            ->label('Total Jam Kerja')
                            ->suffix(' jam')
                            ->formatStateUsing(fn($state) => number_format($state, 2))
                            ->badge()
                            ->color('info')
                            ->icon(Heroicon::OutlinedBriefcase),

                        TextEntry::make('total_late_minutes')
                            ->label('Total Terlambat')
                            ->suffix(' menit')
                            ->formatStateUsing(fn($state) => number_format($state) . ' menit (' . gmdate('H:i', $state * 60) . ')')
                            ->badge()
                            ->color('warning')
                            ->icon(Heroicon::OutlinedClock),
                    ]),

                Section::make('Perhitungan')
                    ->columns(3)
                    ->components([
                        TextEntry::make('total_days')
                            ->label('Total Hari Kerja')
                            ->state(
                                fn($record) =>
                                $record->total_present + $record->total_late + $record->total_absent
                            )
                            ->suffix(' hari')
                            ->badge(),

                        TextEntry::make('attendance_rate')
                            ->label('Tingkat Kehadiran')
                            ->state(function ($record) {
                                $total = $record->total_present + $record->total_late + $record->total_absent;
                                if ($total == 0) return '0%';
                                return number_format(($record->total_present / $total) * 100, 1) . '%';
                            })
                            ->badge()
                            ->color(function ($record) {
                                $total = $record->total_present + $record->total_late + $record->total_absent;
                                if ($total == 0) return 'gray';
                                $rate = ($record->total_present / $total) * 100;
                                if ($rate >= 95) return 'success';
                                if ($rate >= 80) return 'warning';
                                return 'danger';
                            }),

                        TextEntry::make('avg_work_hours')
                            ->label('Rata-rata Jam/Hari')
                            ->state(
                                fn($record) =>
                                $record->total_present > 0
                                    ? number_format($record->total_work_hours / $record->total_present, 2) . ' jam'
                                    : '0 jam'
                            )
                            ->badge()
                            ->color('info'),
                    ]),

                Section::make('Riwayat')
                    ->columns(2)
                    ->components([
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d F Y, H:i'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime('d F Y, H:i'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('month')
                    ->label('Bulan')
                    ->formatStateUsing(
                        fn($state) =>
                        Carbon::create()->month($state)->translatedFormat('F')
                    )
                    ->sortable(),

                TextColumn::make('employee.employee_number')
                    ->label('No. Karyawan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('employee.department.name')
                    ->label('Departemen')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_present')
                    ->label('Hadir')
                    ->suffix(' hari')
                    ->alignCenter()
                    ->sortable()
                    ->color('success'),

                TextColumn::make('total_late')
                    ->label('Telat')
                    ->suffix(' hari')
                    ->alignCenter()
                    ->sortable()
                    ->color('warning'),

                TextColumn::make('total_absent')
                    ->label('Tidak Hadir')
                    ->suffix(' hari')
                    ->alignCenter()
                    ->sortable()
                    ->color('danger'),

                TextColumn::make('total_work_hours')
                    ->label('Total Jam')
                    ->formatStateUsing(fn($state) => number_format($state, 1))
                    ->suffix(' jam')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_late_minutes')
                    ->label('Total Telat')
                    ->formatStateUsing(fn($state) => number_format($state) . ' mnt')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('attendance_rate')
                    ->label('Tingkat Kehadiran')
                    ->state(function ($record) {
                        $total = $record->total_present + $record->total_late + $record->total_absent;
                        if ($total == 0) return 0;
                        return number_format(($record->total_present / $total) * 100, 1);
                    })
                    ->suffix('%')
                    ->badge()
                    ->color(function ($record) {
                        $total = $record->total_present + $record->total_late + $record->total_absent;
                        if ($total == 0) return 'gray';
                        $rate = ($record->total_present / $total) * 100;
                        if ($rate >= 95) return 'success';
                        if ($rate >= 80) return 'warning';
                        return 'danger';
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('year', 'desc')
            ->defaultSort('month', 'desc')
            ->filters([
                SelectFilter::make('year')
                    ->label('Tahun')
                    ->options(function () {
                        $years = [];
                        for ($i = now()->year; $i >= 2020; $i--) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),

                SelectFilter::make('month')
                    ->label('Bulan')
                    ->options([
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember',
                    ]),

                SelectFilter::make('employee')
                    ->label('Karyawan')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('department')
                    ->label('Departemen')
                    ->relationship('employee.department', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAttendanceSummaries::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class AttendanceWidget extends TableWidget
{
    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 3,
    ];

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
            ->poll('5s')
            ->heading('')
            ->striped()
            ->query(
                fn(): Builder =>
                Attendance::query()
                    ->with(['employee.department'])
                    ->latest()
                    ->whereDate('created_at', today())
            )
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Nama Lengkap'),

                TextColumn::make('employee.department.name')
                    ->label('Jabatan')
                    ->badge(),

                TextColumn::make('work_date')
                    ->label('Tanggal')
                    ->date(),

                TextColumn::make('check_in_at')
                    ->dateTime('j-m-Y, H:i')
                    ->label('Masuk'),

                TextColumn::make('check_out_at')
                    ->dateTime('j-m-Y, H:i')
                    ->label('Keluar'),

                // TextColumn::make('work_hours')
                // ->numeric()
                // ->label('Jam Kerja'),

                TextColumn::make('location_in')
                    ->label('Lokasi Masuk')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->getStateUsing(function (Attendance $record) {
                        if (!$record->location_in_lat || !$record->location_in_lng) return '-';

                        $setting = \App\Models\Setting::first();
                        if (!$setting?->latitude || !$setting?->longitude || !$setting?->radius) {
                            return 'Lokasi';
                        }

                        $earthRadius = 6371000;
                        $latDelta = deg2rad($record->location_in_lat - $setting->latitude);
                        $lonDelta = deg2rad($record->location_in_lng - $setting->longitude);
                        $a = sin($latDelta / 2) ** 2 +
                            cos(deg2rad($setting->latitude)) * cos(deg2rad($record->location_in_lat)) *
                            sin($lonDelta / 2) ** 2;
                        $distance = $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));

                        return $distance <= $setting->radius ? 'Lokasi (on site)' : 'Lokasi (diluar radius)';
                    })
                    ->url(fn(Attendance $record) => $record->location_in_lat && $record->location_in_lng
                        ? "https://maps.google.com/?q={$record->location_in_lat},{$record->location_in_lng}"
                        : null)
                    ->openUrlInNewTab()
                    ->color(fn(string $state): string => match ($state) {
                        'Lokasi (on site)' => 'success',
                        'Lokasi (diluar radius)' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('location_out')
                    ->label('Lokasi Keluar')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->getStateUsing(function (Attendance $record) {
                        if (!$record->location_out_lat || !$record->location_out_lng) return '-';

                        $setting = \App\Models\Setting::first();
                        if (!$setting?->latitude || !$setting?->longitude || !$setting?->radius) {
                            return 'Lokasi';
                        }

                        $earthRadius = 6371000;
                        $latDelta = deg2rad($record->location_out_lat - $setting->latitude);
                        $lonDelta = deg2rad($record->location_out_lng - $setting->longitude);
                        $a = sin($latDelta / 2) ** 2 +
                            cos(deg2rad($setting->latitude)) * cos(deg2rad($record->location_out_lat)) *
                            sin($lonDelta / 2) ** 2;
                        $distance = $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));

                        return $distance <= $setting->radius ? 'Lokasi (on site)' : 'Lokasi (diluar radius)';
                    })
                    ->url(fn(Attendance $record) => $record->location_out_lat && $record->location_out_lng
                        ? "https://maps.google.com/?q={$record->location_out_lat},{$record->location_out_lng}"
                        : null)
                    ->openUrlInNewTab()
                    ->color(fn(string $state): string => match ($state) {
                        'Lokasi (on site)' => 'success',
                        'Lokasi (diluar radius)' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    // ->color(fn(string $state): string => match ($state) {
                    //     'present' => 'success',
                    //     'late' => 'warning',
                    //     'absent' => 'danger',
                    //     'incomplete' => 'gray',
                    //     default => 'gray',
                    // })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        'absent' => 'Tidak Hadir',
                        'incomplete' => 'Belum Lengkap',
                        default => $state,
                    }),
            ]);
    }
}

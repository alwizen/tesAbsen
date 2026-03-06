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

            TextColumn::make('work_hours')
            ->numeric()
            ->label('Jam Kerja'),

            TextColumn::make('location_in')
            ->label('Lokasi Masuk')
            ->icon('heroicon-o-arrow-top-right-on-square')
            ->getStateUsing(fn(Attendance $record) => $record->location_in_lat && $record->location_in_lng ? "Lokasi" : '-')
            ->url(fn(Attendance $record) => $record->location_in_lat && $record->location_in_lng ? "https://maps.google.com/?q={$record->location_in_lat},{$record->location_in_lng}" : null)
            ->openUrlInNewTab()
            ->color('info'),
            TextColumn::make('location_out')
            ->label('Lokasi Keluar')
            ->icon('heroicon-o-arrow-top-right-on-square')
            ->getStateUsing(fn(Attendance $record) => $record->location_out_lat && $record->location_out_lng ? "Lokasi" : '-')
            ->url(fn(Attendance $record) => $record->location_out_lat && $record->location_out_lng ? "https://maps.google.com/?q={$record->location_out_lat},{$record->location_out_lng}" : null)
            ->openUrlInNewTab()
            ->color('warning'),

            TextColumn::make('status')
            ->badge()
            ->color(fn(string $state) => match ($state) {
            'present' => 'success',
            'late' => 'warning',
            'absent' => 'danger',
            'incomplete' => 'gray',
            default => 'secondary',
        }),
        ]);
    }
}
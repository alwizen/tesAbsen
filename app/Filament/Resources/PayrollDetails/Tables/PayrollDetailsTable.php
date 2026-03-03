<?php

namespace App\Filament\Resources\PayrollDetails\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PayrollDetailsTable
{
    public static function configure(Table $table): Table
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
                    ->label('No. Relawan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.name')
                    ->label('Nama Relawan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.department.name')
                    ->label('Departemen')
                    ->badge()
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
            ->defaultSort('payroll_id', 'desc')
            ->recordActions([
                Action::make('cetak_slip')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn($record) => route('payroll.slip', $record->id))
                    ->openUrlInNewTab(),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

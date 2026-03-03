<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\ManageAttendances;
use App\Models\Attendance;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
// use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\ExportBulkAction;
use UnitEnum;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationLabel = 'Rekap Absensi';

    protected static ?string $label = 'Rekap Absensi';

    protected static string | UnitEnum | null $navigationGroup = 'Absensi & Relawan';


    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->required(),
                DatePicker::make('work_date')
                    ->required(),
                DateTimePicker::make('check_in_at'),
                DateTimePicker::make('check_out_at'),
                TextInput::make('late_minutes')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('work_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('status')
                    ->required()
                    ->default('incomplete'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('employee.name')
                    ->label('Employee'),
                TextEntry::make('work_date')
                    ->date(),
                TextEntry::make('check_in_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('check_out_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('late_minutes')
                    ->numeric(),
                TextEntry::make('work_hours')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('notes')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->searchable()
                    ->label('Nama Lengkap'),
                TextColumn::make('work_date')
                    ->date()
                    ->label('Tanggal Kerja'),
                TextColumn::make('check_in_at')
                    ->dateTime('j/m/Y, H:i')
                    ->label('Masuk'),
                TextColumn::make('check_out_at')
                    ->dateTime('j/m/Y, H:i')
                    ->label('Keluar'),
                    TextColumn::make('location_in')
                    ->label('Lokasi Masuk')
                    ->getStateUsing(fn (Attendance $record) => $record->location_in_lat && $record->location_in_lng ? "Map" : '-')
                    ->url(fn (Attendance $record) => $record->location_in_lat && $record->location_in_lng ? "https://maps.google.com/?q={$record->location_in_lat},{$record->location_in_lng}" : null)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-map-pin')
                    ->color('info'),
                TextColumn::make('location_out')
                    ->label('Lokasi Keluar')
                    ->getStateUsing(fn (Attendance $record) => $record->location_out_lat && $record->location_out_lng ? "Map" : '-')
                    ->url(fn (Attendance $record) => $record->location_out_lat && $record->location_out_lng ? "https://maps.google.com/?q={$record->location_out_lat},{$record->location_out_lng}" : null)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-map-pin')
                    ->color('warning'),
                TextColumn::make('late_minutes')
                    ->label('Terlambat')
                    ->formatStateUsing(function ($state) {
                        if ($state == 0) {
                            return '-';
                        }

                        $hours = floor($state / 60);
                        $minutes = $state % 60;

                        return sprintf('%02d:%02d', $hours, $minutes);
                    })
                    ->suffix(fn($state) => $state > 0 ? ' jam' : '')
                    // ->color(fn($state) => $state > 0 ? 'warning' : 'success')
                    ->icon(fn($state) => $state > 0 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'),
                TextColumn::make('work_hours')
                    ->label('Jam Kerja')
                    ->numeric()

                    ->suffix(' jam')
                    ->color('info'),
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
                TextColumn::make('created_at')
                    ->dateTime()

                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()

                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Karyawan')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        'absent' => 'Tidak Hadir',
                        'incomplete' => 'Belum Lengkap',
                    ])
                    ->multiple(),

                Filter::make('work_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->native(false),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('work_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('work_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['until'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),

                Filter::make('late')
                    ->label('Terlambat')
                    // ->query(fn(Builder $query): Builder => $query->where('late_minutes', '>', 0))
                    ->toggle(),
            ])
            ->defaultSort('work_date', 'desc')
            // ->recordActions([
            //     ViewAction::make(),
            //     EditAction::make(),
            //     DeleteAction::make(),
            // ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make(),
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAttendances::route('/'),
        ];
    }
}
<?php

namespace App\Filament\Resources\Payrolls;

use App\Filament\Resources\Payrolls\Pages\ManagePayrolls;
use App\Models\Payroll;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use UnitEnum;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationLabel = 'Periode Penggajian';

    protected static string | UnitEnum | null $navigationGroup = 'Gaji Relawan';

    protected static ?string $label = 'Periode Penggajian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('year')
                    ->label('Tahun')
                    ->required()
                    ->numeric()
                    ->minValue(2000)
                    ->maxValue(2099)
                    ->default(now()->year)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        self::updatePeriodDates($get, $set);
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
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        self::updatePeriodDates($get, $set);
                    }),

                DatePicker::make('period_start')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->live()
                    ->afterStateUpdated(
                        fn(Get $get, Set $set) =>
                        $set('period_end', null)
                    ),

                DatePicker::make('period_end')
                    ->label('Tanggal Selesai')
                    ->required()
                    ->afterOrEqual('period_start')
                    ->minDate(fn(Get $get) => $get('period_start')),

                Select::make('status')
                    ->label('Status')
                    ->required()
                    ->options([
                        'draft' => 'Draft',
                        'processed' => 'Diproses',
                        'approved' => 'Disetujui',
                        'paid' => 'Dibayar',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('draft')
                    ->disabled(fn($record) => in_array($record?->status, ['paid', 'cancelled']))
                    ->helperText(
                        fn($record) =>
                        $record?->status === 'paid'
                            ? 'Status tidak dapat diubah karena sudah dibayar'
                            : ($record?->status === 'cancelled'
                                ? 'Status tidak dapat diubah karena sudah dibatalkan'
                                : null)
                    ),

                DateTimePicker::make('processed_at')
                    ->label('Diproses Pada')
                    ->disabled()
                    ->visible(fn($record) => $record?->processed_at !== null)
                    ->placeholder('-'),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->placeholder('Tambahkan catatan jika diperlukan'),
            ]);
    }

    protected static function updatePeriodDates(Get $get, Set $set): void
    {
        $year = $get('year');
        $month = $get('month');

        if ($year && $month) {
            $date = Carbon::create($year, $month, 1);
            $set('period_start', $date->startOfMonth()->toDateString());
            $set('period_end', $date->copy()->endOfMonth()->toDateString());
        }
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informasi Periode')
                    ->columns(2)
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

                        TextEntry::make('period_start')
                            ->label('Tanggal Mulai')
                            ->date('d F Y'),

                        TextEntry::make('period_end')
                            ->label('Tanggal Selesai')
                            ->date('d F Y'),
                    ]),

                Section::make('Status Pemrosesan')
                    ->columns(2)
                    ->components([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'draft' => 'gray',
                                'processed' => 'warning',
                                'approved' => 'info',
                                'paid' => 'success',
                                'cancelled' => 'danger',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'draft' => 'Draft',
                                'processed' => 'Diproses',
                                'approved' => 'Disetujui',
                                'paid' => 'Dibayar',
                                'cancelled' => 'Dibatalkan',
                                default => $state,
                            }),

                        TextEntry::make('processed_at')
                            ->label('Diproses Pada')
                            ->dateTime('d F Y, H:i')
                            ->placeholder('-'),

                        TextEntry::make('processedBy.name')
                            ->label('Diproses Oleh')
                            ->placeholder('-'),
                    ]),

                Section::make('Catatan & Riwayat')
                    ->columnSpanFull()
                    ->components([
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('Tidak ada catatan')
                            ->columnSpanFull(),

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

                TextColumn::make('period_start')
                    ->label('Periode')
                    ->formatStateUsing(
                        fn($record) =>
                        Carbon::parse($record->period_start)->format('d/m/Y') .
                            ' - ' .
                            Carbon::parse($record->period_end)->format('d/m/Y')
                    )
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'processed',
                        'info' => 'approved',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'processed' => 'Diproses',
                        'approved' => 'Disetujui',
                        'paid' => 'Dibayar',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),

                TextColumn::make('processedBy.name')
                    ->label('Diproses Oleh')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('processed_at')
                    ->label('Tanggal Proses')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('year', 'desc')
            ->defaultSort('month', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn($record) => $record->status === 'draft'),
                DeleteAction::make()
                    ->visible(fn($record) => $record->status === 'draft'),
                Action::make('changeStatus')
                    ->label('Ubah Status')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn($record) => $record->status !== 'paid')
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'processed' => 'Processed',
                                'paid' => 'Paid',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => $data['status'],
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Hanya hapus yang masih draft
                            return $records->where('status', 'draft');
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePayrolls::route('/'),
        ];
    }
}

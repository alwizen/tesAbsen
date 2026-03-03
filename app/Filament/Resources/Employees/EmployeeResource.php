<?php

namespace App\Filament\Resources\Employees;

use App\Filament\Resources\Employees\Pages\ManageEmployees;
use App\Models\Employee;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationLabel = 'Daftar Relawan';

    protected static string | UnitEnum | null $navigationGroup = 'Absensi & Relawan';

    protected static ?string $label = 'Daftar Relawan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('rfid_number')
                    ->label('Kartu Absen')
                    ->required(),
                TextInput::make('employee_number')
                    ->label('No Karyawan')
                    ->required()
                    ->default(function () {
                        // Generate nomor karyawan otomatis
                        $lastEmployee = \App\Models\Employee::latest('id')->first();
                        $nextNumber = $lastEmployee ? (int) substr($lastEmployee->employee_number, -4) + 1 : 1;

                        // Format: SPPG-YYYY-0001
                        return 'SPPG-' . date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                    })
                    ->disabled()
                    ->dehydrated()
                    ->hidden(fn(string $operation): bool => $operation === 'edit'),

                TextInput::make('name')
                    ->label('Nama')
                    ->required(),

                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required(),

                TextInput::make('phone')
                    ->label('No. Telp')
                    ->required()
                    ->unique(),

                DatePicker::make('join_date')
                    ->label('Tanggal Bergabung')
                    ->required()
                    ->default(now()),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('rfid_number'),
                TextEntry::make('employee_number'),
                TextEntry::make('name'),
                TextEntry::make('department.name')
                    ->label('Department'),
                    TextEntry::make('phone'),
                TextEntry::make('join_date')
                    ->date(),
                IconEntry::make('is_active')
                    ->boolean(),
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
                TextColumn::make('rfid_number')
                    ->label('Kartu Absen')
                    ->searchable(),
                TextColumn::make('employee_number')
                    ->searchable()
                    ->label('No Karyawan'),
                TextColumn::make('name')
                    ->searchable()
                    ->label('Nama Lengkap'),
                TextColumn::make('department.name')
                    ->searchable()
                    ->label('Jabatan'),
                TextColumn::make('phone')
                    ->label('No. Telp')
                    ->copyable(),
                TextColumn::make('join_date')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => ManageEmployees::route('/'),
        ];
    }
}
<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Pages\ManageDepartments;
use App\Models\Department;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationLabel = 'Daftar Jabatan';

    protected static ?string $label = 'Daftar Jabatan';

    protected static string | UnitEnum | null $navigationGroup = 'Jabatan & Jam Kerja';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->default(function (string $operation) {
                        if ($operation === 'create') {
                            return 'MBG-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                        }
                        return null;
                    })
                    ->hidden(fn(string $operation): bool => $operation === 'edit'),
                Select::make('salary_type')
                    ->label('Tipe Gaji')
                    ->options([
                        'daily' => 'Harian',
                        'hourly' => 'Per Jam',
                    ]),
                TextInput::make('daily_rate')
                    ->label('Gaji Harian'),
                TextInput::make('hourly_rate')
                    ->label('Gaji Per Jam'),
                TextInput::make('allowance')
                    ->label('Tunjangan'),
                TextInput::make('pj_allowance')
                    ->label('Tunjangan PJ'),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('code'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
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
                TextColumn::make('name')
                    ->label('Nama Jabatan'),
                TextColumn::make('code')
                    ->label('Kode Jabatan'),
                TextColumn::make('salary_type')
                    ->label('Tipe Gaji'),
                TextColumn::make('daily_rate')
                    ->label('Gaji Harian')
                    ->money('IDR'),
                TextColumn::make('hourly_rate')
                    ->money('IDR')
                    ->label('Gaji Per Jam'),
                TextColumn::make('allowance')
                    ->money('IDR')
                    ->label('Tunjangan'),
                TextColumn::make('pj_allowance')
                    ->money('IDR')
                    ->label('Tunjangan PJ'),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
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
            'index' => ManageDepartments::route('/'),
        ];
    }
}

<?php

namespace App\Filament\Resources\WorkSchedules;

use App\Filament\Resources\WorkSchedules\Pages\ManageWorkSchedules;
use App\Models\WorkSchedule;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
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

class WorkScheduleResource extends Resource
{
    protected static ?string $model = WorkSchedule::class;

    protected static ?string $navigationLabel = 'Jam Kerja';

    protected static ?string $label = 'Jam Kerja';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string | UnitEnum | null $navigationGroup = 'Jabatan & Jam Kerja';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->required(),
                TimePicker::make('check_in_time')
                    ->required(),
                TimePicker::make('check_out_time')
                    ->required(),
                TextInput::make('grace_period_minutes')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_overnight')
                    ->required(),
                TextInput::make('max_work_hours')
                    ->required()
                    ->numeric()
                    ->default(8),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('department.name')
                    ->label('Department'),
                TextEntry::make('check_in_time')
                    ->time(),
                TextEntry::make('check_out_time')
                    ->time(),
                TextEntry::make('grace_period_minutes')
                    ->numeric(),
                IconEntry::make('is_overnight')
                    ->boolean(),
                TextEntry::make('max_work_hours')
                    ->numeric(),
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
                TextColumn::make('department.name')
                    ->searchable(),
                TextColumn::make('check_in_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('check_out_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('grace_period_minutes')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_overnight')
                    ->boolean(),
                TextColumn::make('max_work_hours')
                    ->numeric()
                    ->sortable(),
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
            'index' => ManageWorkSchedules::route('/'),
        ];
    }
}

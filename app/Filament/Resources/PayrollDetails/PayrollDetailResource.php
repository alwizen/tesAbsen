<?php

namespace App\Filament\Resources\PayrollDetails;

use App\Filament\Resources\PayrollDetails\Pages\CreatePayrollDetail;
use App\Filament\Resources\PayrollDetails\Pages\EditPayrollDetail;
use App\Filament\Resources\PayrollDetails\Pages\ListPayrollDetails;
use App\Filament\Resources\PayrollDetails\Pages\ViewPayrollDetail;
use App\Filament\Resources\PayrollDetails\Schemas\PayrollDetailForm;
use App\Filament\Resources\PayrollDetails\Schemas\PayrollDetailInfolist;
use App\Filament\Resources\PayrollDetails\Tables\PayrollDetailsTable;
use App\Models\PayrollDetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PayrollDetailResource extends Resource
{
    protected static ?string $model = PayrollDetail::class;

    protected static ?string $navigationLabel = 'Penggajian';

    protected static string | UnitEnum | null $navigationGroup = 'Gaji Relawan';

    protected static ?string $label = 'Penggajian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    public static function form(Schema $schema): Schema
    {
        return PayrollDetailForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PayrollDetailInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollDetailsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrollDetails::route('/'),
            'create' => CreatePayrollDetail::route('/create'),
            'view' => ViewPayrollDetail::route('/{record}'),
            'edit' => EditPayrollDetail::route('/{record}/edit'),
        ];
    }
}

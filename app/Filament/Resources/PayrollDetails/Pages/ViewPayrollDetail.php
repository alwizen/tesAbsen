<?php

namespace App\Filament\Resources\PayrollDetails\Pages;

use App\Filament\Resources\PayrollDetails\PayrollDetailResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPayrollDetail extends ViewRecord
{
    protected static string $resource = PayrollDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

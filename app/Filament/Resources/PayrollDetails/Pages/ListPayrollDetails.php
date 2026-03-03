<?php

namespace App\Filament\Resources\PayrollDetails\Pages;

use App\Filament\Resources\PayrollDetails\PayrollDetailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayrollDetails extends ListRecords
{
    protected static string $resource = PayrollDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

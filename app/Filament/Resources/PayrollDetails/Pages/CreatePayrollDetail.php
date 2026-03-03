<?php

namespace App\Filament\Resources\PayrollDetails\Pages;

use App\Filament\Resources\PayrollDetails\PayrollDetailResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayrollDetail extends CreateRecord
{
    protected static string $resource = PayrollDetailResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

<?php

namespace App\Filament\Resources\AttendanceSummaries\Pages;

use App\Filament\Resources\AttendanceSummaries\AttendanceSummaryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAttendanceSummaries extends ManageRecords
{
    protected static string $resource = AttendanceSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

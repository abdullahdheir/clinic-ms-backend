<?php

namespace App\Filament\Resources\MedicalReports\Pages;

use App\Filament\Resources\MedicalReports\MedicalReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedicalReports extends ListRecords
{
    protected static string $resource = MedicalReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

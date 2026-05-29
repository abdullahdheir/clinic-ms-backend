<?php

namespace App\Filament\Resources\MedicalReports\Pages;

use App\Filament\Resources\MedicalReports\MedicalReportResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditMedicalReport extends EditRecord
{
    protected static string $resource = MedicalReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

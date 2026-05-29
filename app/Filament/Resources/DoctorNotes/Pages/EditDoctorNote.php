<?php

namespace App\Filament\Resources\DoctorNotes\Pages;

use App\Filament\Resources\DoctorNotes\DoctorNoteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditDoctorNote extends EditRecord
{
    protected static string $resource = DoctorNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

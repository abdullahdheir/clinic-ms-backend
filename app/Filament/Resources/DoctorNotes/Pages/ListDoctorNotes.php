<?php

namespace App\Filament\Resources\DoctorNotes\Pages;

use App\Filament\Resources\DoctorNotes\DoctorNoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDoctorNotes extends ListRecords
{
    protected static string $resource = DoctorNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\DoctorNotes\Pages;

use App\Filament\Resources\DoctorNotes\DoctorNoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDoctorNote extends CreateRecord
{
    protected static string $resource = DoctorNoteResource::class;
}

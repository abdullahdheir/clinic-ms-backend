<?php

namespace App\Filament\Resources\ClinicResource\RelationManagers;

use App\Filament\Resources\DoctorResource\DoctorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class DoctorsRelationManager extends RelationManager
{
    protected static string $relationship = 'doctors';

    protected static ?string $relatedResource = DoctorResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}

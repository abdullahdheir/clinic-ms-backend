<?php

namespace App\Filament\Resources\DoctorResource\RelationManagers;

use App\Filament\Resources\ClinicResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ClinicsRelationManager extends RelationManager
{
    protected static string $relationship = 'clinics';

    protected static ?string $relatedResource = ClinicResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}

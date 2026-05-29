<?php

namespace App\Filament\Resources\MedicalReports;

use App\Filament\Resources\MedicalReports\Pages\CreateMedicalReport;
use App\Filament\Resources\MedicalReports\Pages\EditMedicalReport;
use App\Filament\Resources\MedicalReports\Pages\ListMedicalReports;
use App\Filament\Resources\MedicalReports\Schemas\MedicalReportForm;
use App\Filament\Resources\MedicalReports\Tables\MedicalReportsTable;
use App\Models\MedicalReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicalReportResource extends Resource
{
    protected static ?string $model = MedicalReport::class;

    protected static ?string $navigationLabel = 'التقارير الطبية';
    protected static ?string $modelLabel = 'تقرير طبي';
    protected static ?string $pluralModelLabel = 'التقارير الطبية';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocument;
    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return MedicalReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicalReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicalReports::route('/'),
            'create' => CreateMedicalReport::route('/create'),
            'edit' => EditMedicalReport::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

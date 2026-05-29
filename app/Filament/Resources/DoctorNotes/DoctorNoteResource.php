<?php

namespace App\Filament\Resources\DoctorNotes;

use App\Filament\Resources\DoctorNotes\Pages\CreateDoctorNote;
use App\Filament\Resources\DoctorNotes\Pages\EditDoctorNote;
use App\Filament\Resources\DoctorNotes\Pages\ListDoctorNotes;
use App\Filament\Resources\DoctorNotes\Schemas\DoctorNoteForm;
use App\Filament\Resources\DoctorNotes\Tables\DoctorNotesTable;
use App\Models\DoctorNote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DoctorNoteResource extends Resource
{
    protected static ?string $model = DoctorNote::class;

    protected static ?string $navigationLabel = 'ملاحظات الأطباء';
    protected static ?string $modelLabel = 'ملاحظة طبيب';
    protected static ?string $pluralModelLabel = 'ملاحظات الأطباء';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;
    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return DoctorNoteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DoctorNotesTable::configure($table);
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
            'index' => ListDoctorNotes::route('/'),
            'create' => CreateDoctorNote::route('/create'),
            'edit' => EditDoctorNote::route('/{record}/edit'),
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

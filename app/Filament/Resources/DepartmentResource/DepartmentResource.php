<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationLabel = 'الأقسام';
    protected static ?string $modelLabel = 'قسم';
    protected static ?string $pluralModelLabel = 'الأقسام';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('معلومات القسم')->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('اسم القسم')
                        ->required(),
                    Forms\Components\TextInput::make('specialty')
                        ->label('التخصص'),
                    Forms\Components\TextInput::make('max_capacity')
                        ->label('السعة القصوى')
                        ->numeric()
                        ->default(20),
                    Forms\Components\Textarea::make('description')
                        ->label('الوصف')
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('القسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialty')
                    ->label('التخصص')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_capacity')
                    ->label('السعة القصوى')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clinics_count')
                    ->counts('clinics')
                    ->label('العيادات')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('doctors_count')
                    ->counts('doctors')
                    ->label('الأطباء')
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('clinic')
                    ->label('العيادة')
                    ->relationship('clinics', 'name'),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DoctorsRelationManager::class,
            RelationManagers\ClinicsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}

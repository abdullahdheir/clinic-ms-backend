<?php

namespace App\Filament\Resources\DoctorResource;

use App\Models\Doctor;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static ?string $navigationLabel = 'الأطباء';
    protected static ?string $modelLabel = 'طبيب';
    protected static ?string $pluralModelLabel = 'الأطباء';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('معلومات الطبيب')->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('المستخدم')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('specialization')
                        ->label('التخصص')
                        ->required(),
                    Forms\Components\Textarea::make('bio')
                        ->label('السيرة الذاتية')
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('الأقسام')->schema([
                    Forms\Components\CheckboxList::make('departments')
                        ->label('الأقسام')
                        ->relationship('departments', 'name')
                        ->columns(3),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الطبيب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialization')
                    ->label('التخصص')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clinics_count')
                    ->counts('clinics')
                    ->label('العيادات')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('departments_count')
                    ->counts('departments')
                    ->label('الأقسام')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('appointments_count')
                    ->counts('appointments')
                    ->label('المواعيد')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('prescriptions_count')
                    ->counts('prescriptions')
                    ->label('الوصفات')
                    ->badge()
                    ->color('warning'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('clinic')
                    ->label('العيادة')
                    ->relationship('clinics', 'name'),
                Tables\Filters\SelectFilter::make('department')
                    ->label('القسم')
                    ->relationship('departments', 'name'),
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
            RelationManagers\ClinicsRelationManager::class,
            RelationManagers\DepartmentsRelationManager::class,
            RelationManagers\ShiftsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}

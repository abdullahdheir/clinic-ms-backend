<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorResource\Pages;
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

    protected static ?string $navigationLabel  = 'الأطباء';
    protected static ?int $navigationSort      = 2;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('معلومات الطبيب')->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('المستخدم')
                        ->relationship('user','name')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('department_id')
                        ->label('القسم')
                        ->relationship('department','name')
                        ->searchable(),
                    Forms\Components\TextInput::make('specialization')
                        ->label('التخصص')
                        ->required(),
                    Forms\Components\TextInput::make('session_duration_minutes')
                        ->label('مدة الجلسة (دقيقة)')
                        ->numeric()
                        ->default(30),
                    Forms\Components\TextInput::make('consultation_fee')
                        ->label('رسورة الاستشارة')
                        ->numeric()
                        ->prefix('$'),
                    Forms\Components\Textarea::make('bio')
                        ->label('السيرة الذاتية')
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الطبيب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialization')
                    ->label('التخصص'),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('القسم'),
                Tables\Columns\TextColumn::make('session_duration_minutes')
                    ->label('مدة الجلسة')
                    ->suffix(' دقيقة'),
                Tables\Columns\TextColumn::make('consultation_fee')
                    ->label('رسورة الاستشارة')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('appointments_count')
                    ->counts('appointments')
                    ->label('المواعيد')
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department','name'),
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
            //
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

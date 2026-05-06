<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClinicResource\Pages;
use App\Models\Clinic;
use App\Models\User;
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

class ClinicResource extends Resource
{
    protected static ?string $model = Clinic::class;

    protected static ?string $navigationLabel   = 'الأقسام';
    protected static ?string $modelLabel        = 'عيادة';
    protected static ?string $pluralModelLabel  = 'العيادات';
    protected static ?int $navigationSort       = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('معلومات العيادة')->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('اسم العيادة')
                        ->required(),
                    Forms\Components\TextInput::make('phone')
                        ->label('رقم الهاتف'),
                    Forms\Components\Textarea::make('address')
                        ->label('العنوان')
                        ->columnSpanFull(),
                    Forms\Components\Select::make('manager_id')
                        ->label('المدير')
                        ->options(User::role('manager')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('نشطة')
                        ->default(true),
                ])->columns(2),

                Section::make('ساعات العمل')->schema([
                    Forms\Components\KeyValue::make('working_hours')
                        ->label('الجدول الأسبوعي')
                        ->keyLabel('اليوم')
                        ->valueLabel('الأوقات')
                        ->columnSpanFull(),
                ]),

                Section::make('الشعار')->schema([
                    Forms\Components\SpatieMediaLibraryFileUpload::make('logo')
                        ->label('شعار العيادة')
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('1:1')
                        ->columnSpanFull(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('العيادة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('manager.name')
                    ->label('المدير')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف'),
                Tables\Columns\TextColumn::make('departments_count')
                    ->counts('departments')
                    ->label('الأقسام')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('doctors_count')
                    ->counts('doctors')
                    ->label('الأطباء')
                    ->badge()
                    ->color('success'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('الحالة')
                    ->options([1 => 'نشطة', 0 => 'غير نشطة']),
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
            'index' => Pages\ListClinics::route('/'),
            'create' => Pages\CreateClinic::route('/create'),
            'edit' => Pages\EditClinic::route('/{record}/edit'),
        ];
    }
}

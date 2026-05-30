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

                Section::make('العيادات')->schema([
                    Forms\Components\Repeater::make('clinics')
                        ->label('العيادات')
                        ->relationship('clinics')
                        ->schema([
                            Forms\Components\Select::make('clinic_id')
                                ->label('العيادة')
                                ->options(\App\Models\Clinic::all(['id', 'name'])->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->required()
                                ->live(),
                            Forms\Components\Select::make('department_id')
                                ->label('القسم في العيادة')
                                ->options(\App\Models\Department::all(['id', 'name'])->pluck('name', 'id')->toArray())
                                ->searchable(),
                            Forms\Components\TextInput::make('consultation_fee')
                                ->label('رسوم الاستشارة')
                                ->numeric()
                                ->prefix('$')
                                ->default(0),
                            Forms\Components\TextInput::make('session_duration_minutes')
                                ->label('مدة الجلسة (دقيقة)')
                                ->numeric()
                                ->default(30),
                            Forms\Components\Toggle::make('is_active')
                                ->label('نشط')
                                ->default(true),
                            Forms\Components\Repeater::make('shifts')
                                ->label('ساعات العمل')
                                ->schema([
                                    Forms\Components\Select::make('day_of_week')
                                        ->label('اليوم')
                                        ->options([
                                            'saturday' => 'السبت',
                                            'sunday' => 'الأحد',
                                            'monday' => 'الاثنين',
                                            'tuesday' => 'الثلاثاء',
                                            'wednesday' => 'الأربعاء',
                                            'thursday' => 'الخميس',
                                            'friday' => 'الجمعة',
                                        ])
                                        ->required()
                                        ->searchable(),
                                    Forms\Components\TimePicker::make('start_time')
                                        ->label('وقت البداية')
                                        ->seconds(false)
                                        ->required(),
                                    Forms\Components\TimePicker::make('end_time')
                                        ->label('وقت النهاية')
                                        ->seconds(false)
                                        ->required(),
                                    Forms\Components\Toggle::make('is_active')
                                        ->label('نشط')
                                        ->default(true),
                                ])
                                ->columns(4)
                                ->columnSpanFull()
                                ->itemLabel(fn(array $state): ?string => $state['day_of_week'] ?? null),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
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

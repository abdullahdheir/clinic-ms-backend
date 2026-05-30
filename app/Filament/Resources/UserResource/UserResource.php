<?php

namespace App\Filament\Resources\UserResource;

use App\Models\User;
use Filament\Actions\DeleteAction;
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
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel  = 'المستخدمون';
    protected static ?string $modelLabel       = 'مستخدم';
    protected static ?string $pluralModelLabel = 'المستخدمون';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort      = 5;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('البيانات الأساسية')->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('الاسم الكامل')
                        ->required(),
                    Forms\Components\TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('password')
                        ->label('كلمة المرور')
                        ->password()
                        ->dehydrateStateUsing(fn($s) => Hash::make($s))
                        ->dehydrated(fn($s) => filled($s))
                        ->required(fn(string $context) => $context === 'create'),
                    Forms\Components\TextInput::make('phone')
                        ->label('رقم الهاتف'),
                    Forms\Components\TextInput::make('national_id')
                        ->label('رقم الهوية الوطنية'),
                    Forms\Components\DatePicker::make('date_of_birth')
                        ->label('تاريخ الميلاد'),
                    Forms\Components\Select::make('gender')
                        ->label('الجنس')
                        ->options([
                            'male' => 'ذكر',
                            'female' => 'أنثى',
                        ]),
                ])->columns(2),

                Section::make('الصلاحيات')->schema([
                    Forms\Components\Select::make('roles')
                        ->relationship('roles','name')
                        ->label('الدور')
                        ->native(false)
                        ->required(),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles')
                    ->label('الدور')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state->name) {
                        'super_admin'  => 'مسؤول النظام',
                        'manager'      => 'مدير عيادة',
                        'doctor'       => 'طبيب',
                        'receptionist' => 'استقبال',
                        'patient'      => 'مريض',
                        default        => $state,
                    })
                    ->color(fn($state) => match ($state->name) {
                        'super_admin'  => 'danger',
                        'manager'      => 'warning',
                        'doctor'       => 'success',
                        'receptionist' => 'info',
                        'patient'      => 'primary',
                        default        => 'gray',
                    }),
                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('national_id')
                    ->label('رقم الهوية')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('gender')
                    ->label('الجنس')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('الدور')
                    ->options([
                        'super_admin'  => 'مسؤول النظام',
                        'manager'      => 'مدير عيادة',
                        'doctor'       => 'طبيب',
                        'receptionist' => 'استقبال',
                        'patient'      => 'مريض',
                    ]),
                Tables\Filters\SelectFilter::make('gender')
                    ->label('الجنس')
                    ->options([
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

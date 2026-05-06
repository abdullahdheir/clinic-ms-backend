<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
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
    protected static ?int $navigationSort      = 1;

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
                ])->columns(2),

                Section::make('الصلاحيات')->schema([
                    Forms\Components\Select::make('roles')
                        ->label('الدور')
                        ->relationship('roles','name')
                        ->options([
                            'super_admin'  => 'مسؤول النظام',
                            'manager'      => 'مدير عيادة',
                            'doctor'       => 'طبيب',
                            'receptionist' => 'موظف استقبال',
                            'patient'      => 'مريض',
                        ])
                        ->multiple()
                        ->preloaded()
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('الدور')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'super_admin'  => 'مسؤول النظام',
                        'manager'      => 'مدير عيادة',
                        'doctor'       => 'طبيب',
                        'receptionist' => 'استقبال',
                        'patient'      => 'مريض',
                        default        => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'super_admin'  => 'danger',
                        'manager'      => 'warning',
                        'doctor'       => 'success',
                        'receptionist' => 'info',
                        'patient'      => 'primary',
                        default        => 'gray',
                    }),
                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('الدور')
                    ->relationship('roles','name'),
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

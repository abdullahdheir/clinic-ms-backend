<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationLabel  = 'المواعيد';
    protected static ?string $modelLabel       = 'موعد';
    protected static ?string $pluralModelLabel = 'المواعيد';
    protected static ?int $navigationSort      = 2;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('تفاصيل الموعد')->schema([
                Forms\Components\Select::make('patient_id')
                    ->label('المريض')
                    ->relationship('patient', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('doctor_id')
                    ->label('الطبيب')
                    ->relationship('doctor.user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('clinic_id')
                    ->label('العيادة')
                    ->relationship('clinic', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('تاريخ ووقت الموعد')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'     => 'انتظار',
                        'confirmed'   => 'مؤكّد',
                        'in_progress' => 'جارٍ',
                        'done'        => 'منتهي',
                        'cancelled'   => 'ملغي',
                        'no_show'     => 'لم يحضر',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([

            Tables\Columns\TextColumn::make('patient.name')
                ->label('المريض')
                ->searchable(),
            Tables\Columns\TextColumn::make('doctor.user.name')
                ->label('الطبيب'),
            Tables\Columns\TextColumn::make('clinic.name')
                ->label('العيادة'),
            Tables\Columns\TextColumn::make('scheduled_at')
                ->label('الموعد')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
            Tables\Columns\TextColumn::make('status')
                ->label('الحالة')
                ->badge()
                ->formatStateUsing(fn($s) => match ($s) {
                    'confirmed'   => 'مؤكّد',
                    'pending'     => 'انتظار',
                    'cancelled'   => 'ملغي',
                    'done'        => 'منتهي',
                    'in_progress' => 'جارٍ',
                    'no_show'     => 'لم يحضر',
                    default       => $s,
                })
                ->color(fn($s) => match ($s) {
                    'confirmed'   => 'success',
                    'pending'     => 'warning',
                    'cancelled'   => 'danger',
                    'done'        => 'primary',
                    'in_progress' => 'info',
                    default       => 'gray',
                }),
        ])
            ->recordActions([
                EditAction::make()->label('تعديل'),
                Action::make('confirm')
                    ->label('تأكيد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->action(fn($record) => $record->update(['status' => 'confirmed'])),
                Action::make('cancel')
                    ->label('إلغاء')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => !in_array($record->status, ['cancelled', 'done']))
                    ->action(fn($record) => $record->update(['status' => 'cancelled'])),
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}

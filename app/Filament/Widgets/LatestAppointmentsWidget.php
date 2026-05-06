<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;

class LatestAppointmentsWidget extends BaseTableWidget
{
    protected static ?string $heading = 'آخر المواعيد';
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::with(['patient', 'doctor.user', 'clinic'])
                    ->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('patient.name')
                    ->label('المريض')
                    ->searchable()
                    ->icon('heroicon-m-user')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('الطبيب')
                    ->icon('heroicon-m-heart'),

                Tables\Columns\TextColumn::make('clinic.name')
                    ->label('العيادة')
                    ->icon('heroicon-m-building-office-2')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('الموعد')
                    ->dateTime('d/m/Y H:i')
                    ->icon('heroicon-m-clock')
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'confirmed'   => 'success',
                        'pending'     => 'warning',
                        'cancelled'   => 'danger',
                        'done'        => 'primary',
                        'in_progress' => 'info',
                        'no_show'     => 'gray',
                        default       => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'confirmed'   => 'heroicon-m-check-circle',
                        'pending'     => 'heroicon-m-clock',
                        'cancelled'   => 'heroicon-m-x-circle',
                        'done'        => 'heroicon-m-check-badge',
                        'in_progress' => 'heroicon-m-arrow-path',
                        'no_show'     => 'heroicon-m-exclamation-triangle',
                        default       => 'heroicon-m-question-mark-circle',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'confirmed'   => 'مؤكّد',
                        'pending'     => 'انتظار',
                        'cancelled'   => 'ملغي',
                        'done'        => 'منتهي',
                        'in_progress' => 'جارٍ',
                        'no_show'     => 'لم يحضر',
                        default       => $state,
                    }),
            ])
            ->paginated(false)
            ->striped();
    }
}

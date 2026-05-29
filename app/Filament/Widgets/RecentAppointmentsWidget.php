<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentAppointmentsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'المواعيد القادمة';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->where('scheduled_at', '>=', now())
                    ->orderBy('scheduled_at')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('patient.name')
                    ->label('المريض')
                    ->searchable(),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('الطبيب')
                    ->searchable(),
                Tables\Columns\TextColumn::make('clinic.name')
                    ->label('العيادة')
                    ->searchable(),
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
            ->paginated(false);
    }
}

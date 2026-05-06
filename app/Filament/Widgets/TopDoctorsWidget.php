<?php

namespace App\Filament\Widgets;

use App\Models\Doctor;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseTableWidget;

class TopDoctorsWidget extends BaseTableWidget
{
    protected static ?string $heading = 'أكثر الأطباء مواعيد';
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Doctor::withCount('appointments')
                    ->orderByDesc('appointments_count')->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الطبيب')
                    ->icon('heroicon-m-user-circle')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('specialization')
                    ->label('التخصص')
                    ->icon('heroicon-m-academic-cap')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('القسم')
                    ->icon('heroicon-m-building-library')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('appointments_count')
                    ->label('عدد المواعيد')
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),
            ])
            ->paginated(false)
            ->striped();
    }
}

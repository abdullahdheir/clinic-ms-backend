<?php

namespace App\Filament\Resources\Patients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('المريض')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('national_id')
                    ->label('رقم الهوية')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_of_birth')
                    ->label('تاريخ الميلاد')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gender')
                    ->label('الجنس')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                        default => $state,
                    })
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('blood_type')
                    ->label('فصيلة الدم')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('insurance_company')
                    ->label('شركة التأمين')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('appointments_count')
                    ->counts('appointments')
                    ->label('المواعيد')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('prescriptions_count')
                    ->counts('prescriptions')
                    ->label('الوصفات')
                    ->badge()
                    ->color('success'),
                TextColumn::make('medicalReports_count')
                    ->counts('medicalReports')
                    ->label('التقارير')
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('clinic')
                    ->label('العيادة')
                    ->relationship('appointments.clinic', 'name'),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }
}

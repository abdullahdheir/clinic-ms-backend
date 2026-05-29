<?php

namespace App\Filament\Resources\MedicalReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MedicalReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('patient.name')
                    ->label('المريض')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('doctor.user.name')
                    ->label('الطبيب')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('report_type')
                    ->label('نوع التقرير')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'lab' => 'تحليل مخبري',
                        'xray' => 'أشعة سينية',
                        'mri' => 'رنين مغناطيسي',
                        'ct' => 'تصوير مقطعي',
                        'ultrasound' => 'موجات فوق صوتية',
                        'other' => 'أخرى',
                        default => $state,
                    }),
                TextColumn::make('report_date')
                    ->label('تاريخ التقرير')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'reviewed' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'معلق',
                        'completed' => 'مكتمل',
                        'reviewed' => 'تم المراجعة',
                        default => $state,
                    }),
                IconColumn::make('file_path')
                    ->label('ملف')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('report_type')
                    ->label('نوع التقرير')
                    ->options([
                        'lab' => 'تحليل مخبري',
                        'xray' => 'أشعة سينية',
                        'mri' => 'رنين مغناطيسي',
                        'ct' => 'تصوير مقطعي',
                        'ultrasound' => 'موجات فوق صوتية',
                        'other' => 'أخرى',
                    ]),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'معلق',
                        'completed' => 'مكتمل',
                        'reviewed' => 'تم المراجعة',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\DoctorNotes\Tables;

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

class DoctorNotesTable
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
                TextColumn::make('content')
                    ->label('المحتوى')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('note_type')
                    ->label('نوع الملاحظة')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'general' => 'عام',
                        'follow_up' => 'متابعة',
                        'urgent' => 'عاجل',
                        'routine' => 'روتيني',
                        default => $state,
                    }),
                IconColumn::make('is_pinned')
                    ->label('مثبت')
                    ->boolean()
                    ->trueIcon('heroicon-o-pin')
                    ->falseIcon('heroicon-o-pin-off')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('note_type')
                    ->label('نوع الملاحظة')
                    ->options([
                        'general' => 'عام',
                        'follow_up' => 'متابعة',
                        'urgent' => 'عاجل',
                        'routine' => 'روتيني',
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

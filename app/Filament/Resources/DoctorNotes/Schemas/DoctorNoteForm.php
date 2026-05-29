<?php

namespace App\Filament\Resources\DoctorNotes\Schemas;

use App\Models\Doctor;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DoctorNoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الأساسية')
                    ->schema([
                        Select::make('patient_id')
                            ->label('المريض')
                            ->options(User::where('role', 'patient')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('doctor_id')
                            ->label('الطبيب')
                            ->options(Doctor::with('user')->get()->pluck('user.name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('note_type')
                            ->label('نوع الملاحظة')
                            ->options([
                                'general' => 'عام',
                                'follow_up' => 'متابعة',
                                'urgent' => 'عاجل',
                                'routine' => 'روتيني',
                            ])
                            ->default('general')
                            ->required(),
                        Toggle::make('is_pinned')
                            ->label('تثبيت الملاحظة')
                            ->default(false),
                    ])
                    ->columns(2),
                Section::make('المحتوى')
                    ->schema([
                        Textarea::make('content')
                            ->label('محتوى الملاحظة')
                            ->rows(5)
                            ->required(),
                    ]),
            ]);
    }
}

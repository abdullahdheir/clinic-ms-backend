<?php

namespace App\Filament\Resources\Prescriptions\Schemas;

use App\Models\Doctor;
use App\Models\User;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class PrescriptionForm
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
                        DatePicker::make('prescription_date')
                            ->label('تاريخ الوصفة')
                            ->default(now())
                            ->required(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'active' => 'نشط',
                                'completed' => 'مكتمل',
                                'cancelled' => 'ملغي',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('التشخيص والملاحظات')
                    ->schema([
                        Textarea::make('diagnosis')
                            ->label('التشخيص')
                            ->rows(3)
                            ->required(),
                        Textarea::make('notes')
                            ->label('ملاحظات إضافية')
                            ->rows(2),
                    ]),
            ]);
    }
}

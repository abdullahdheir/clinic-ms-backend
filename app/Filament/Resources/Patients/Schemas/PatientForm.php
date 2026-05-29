<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الشخصية')->schema([
                    Select::make('user_id')
                        ->label('المستخدم')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    TextInput::make('national_id')
                        ->label('رقم الهوية الوطنية'),
                    DatePicker::make('date_of_birth')
                        ->label('تاريخ الميلاد'),
                    Select::make('gender')
                        ->label('الجنس')
                        ->options([
                            'male' => 'ذكر',
                            'female' => 'أنثى',
                        ]),
                    TextInput::make('blood_type')
                        ->label('فصيلة الدم'),
                    TextInput::make('emergency_contact_name')
                        ->label('اسم جهة الاتصال الطارئ'),
                    TextInput::make('emergency_contact_phone')
                        ->label('رقم جهة الاتصال الطارئ')
                        ->tel(),
                ])->columns(2),

                Section::make('البيانات الطبية')->schema([
                    Textarea::make('medical_history')
                        ->label('التاريخ المرضي')
                        ->columnSpanFull(),
                    Textarea::make('allergies')
                        ->label('الحساسية')
                        ->columnSpanFull(),
                    Textarea::make('chronic_medications')
                        ->label('الأدوية المزمنة')
                        ->columnSpanFull(),
                ]),

                Section::make('معلومات التأمين')->schema([
                    TextInput::make('insurance_company')
                        ->label('شركة التأمين'),
                    TextInput::make('insurance_policy_number')
                        ->label('رقم البوليصة'),
                ])->columns(2),
            ]);
    }
}

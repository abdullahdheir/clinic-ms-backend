<?php

namespace App\Filament\Resources\MedicalReports\Schemas;

use App\Models\Doctor;
use App\Models\Patient;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MedicalReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الأساسية')
                    ->schema([
                        Select::make('patient_id')
                            ->label('المريض')
                            ->options(Patient::with('user')->get()->pluck('user.name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('doctor_id')
                            ->label('الطبيب')
                            ->options(Doctor::with('user')->get()->pluck('user.name', 'id'))
                            ->searchable()
                            ->required(),
                        DatePicker::make('report_date')
                            ->label('تاريخ التقرير')
                            ->default(now())
                            ->required(),
                        Select::make('report_type')
                            ->label('نوع التقرير')
                            ->options([
                                'lab' => 'تحليل مخبري',
                                'xray' => 'أشعة سينية',
                                'mri' => 'رنين مغناطيسي',
                                'ct' => 'تصوير مقطعي',
                                'ultrasound' => 'موجات فوق صوتية',
                                'other' => 'أخرى',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending' => 'معلق',
                                'completed' => 'مكتمل',
                                'reviewed' => 'تم المراجعة',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('تفاصيل التقرير')
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان التقرير')
                            ->required(),
                        Textarea::make('description')
                            ->label('الوصف')
                            ->rows(3)
                            ->required(),
                        Textarea::make('results')
                            ->label('النتائج')
                            ->rows(3),
                        Textarea::make('recommendations')
                            ->label('التوصيات')
                            ->rows(2),
                    ]),
                Section::make('الملفات')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('رفع ملف التقرير')
                            ->directory('medical-reports')
                            ->acceptedFileTypes(['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'])
                            ->maxSize(10240),
                    ])
                    ->collapsible(),
            ]);
    }
}

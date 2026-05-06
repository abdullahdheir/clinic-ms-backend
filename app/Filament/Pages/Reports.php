<?php

namespace App\Filament\Pages;

use App\Exports\AppointmentsExport;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use Filament\Actions\Action;
use Filament\Actions\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class Reports extends Page
{
    protected static ?string $navigationLabel = 'التقارير';
    protected static ?string $title           = 'التقارير والإحصائيات';

    protected string $view = 'filament.pages.reports';

    public $from_date;
    public $to_date;

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('مرشح التاريخ')->schema([
                    DatePicker::make('from_date')
                        ->label('من تاريخ')
                        ->required(),
                    DatePicker::make('to_date')
                        ->label('إلى تاريخ')
                        ->required(),
                ])->columns(2),
            ])
            ->state([
                'from_date' => $this->from_date,
                'to_date' => $this->to_date,
            ]);
    }

    public function getReportData()
    {
        $query = Appointment::query();

        if ($this->from_date && $this->to_date) {
            $query->whereBetween('scheduled_at', [$this->from_date, $this->to_date]);
        }

        return [
            'appointments_by_clinic' => $query->join('clinics', 'appointments.clinic_id', '=', 'clinics.id')
                ->select('clinics.name', DB::raw('count(*) as count'))
                ->groupBy('clinics.id', 'clinics.name')
                ->orderByDesc('count')
                ->get(),

            'top_doctors' => $query->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
                ->join('users', 'doctors.user_id', '=', 'users.id')
                ->select('users.name', 'doctors.specialization', DB::raw('count(*) as count'))
                ->groupBy('doctors.id', 'users.id', 'doctors.specialization')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),

            'appointments_by_status' => $query->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
        ];
    }

    public function exportExcel()
    {
        return Excel::download(new AppointmentsExport, 'appointments.xlsx');
    }

    protected function getActions(): array
    {
        return [
            Action::make('export')
                ->label('تصدير Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn() => $this->exportExcel()),
        ];
    }
}

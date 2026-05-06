<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Visit;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use ApiResponse;

    /**
     * Get dashboard statistics
     *
     * @return \Illuminate\Http\JsonResponse - Dashboard statistics
     */
    public function dashboard()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Today's appointments count
        $todayAppointments = Appointment::whereDate('appointment_time', $today)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->count();

        // Total patients count
        $totalPatients = User::where('role', 'patient')->count();

        // Active doctors count
        $activeDoctors = User::where('role', 'doctor')->count();

        // Monthly revenue
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_amount');

        // Appointments by status
        $appointmentsByStatus = Appointment::select('status', DB::raw('count(*) as count'))
            ->whereIn('status', ['confirmed', 'pending', 'cancelled'])
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Appointments last 30 days
        $appointmentsLast30Days = Appointment::select(
                DB::raw('DATE(appointment_time) as date'),
                DB::raw('count(*) as count')
            )
            ->where('appointment_time', '>=', $thirtyDaysAgo)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                ];
            })
            ->values();

        // Top doctors by appointment count
        $topDoctors = Appointment::select(
                'users.id',
                'users.name as doctor',
                DB::raw('count(appointments.id) as appointment_count')
            )
            ->join('users', 'appointments.doctor_id', '=', 'users.id')
            ->where('appointments.appointment_time', '>=', $thirtyDaysAgo)
            ->groupBy('users.id', 'users.name')
            ->orderBy('appointment_count', 'desc')
            ->limit(5)
            ->get();

        return $this->successResponse([
            'today_appointments' => $todayAppointments,
            'total_patients' => $totalPatients,
            'active_doctors' => $activeDoctors,
            'monthly_revenue' => $monthlyRevenue,
            'appointments_by_status' => [
                'confirmed' => $appointmentsByStatus['confirmed'] ?? 0,
                'pending' => $appointmentsByStatus['pending'] ?? 0,
                'cancelled' => $appointmentsByStatus['cancelled'] ?? 0,
            ],
            'appointments_last_30_days' => $appointmentsLast30Days,
            'top_doctors' => $topDoctors,
        ]);
    }

    /**
     * Get appointments report
     *
     * @param Request $request - Date range and clinic filters
     * @return \Illuminate\Http\JsonResponse - Appointments report data
     */
    public function appointments(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'clinic_id' => 'nullable|exists:clinics,id',
        ]);

        $query = Appointment::with(['patient.user', 'doctor.user', 'clinic']);

        // Apply date filters
        if ($request->from) {
            $query->whereDate('appointment_time', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('appointment_time', '<=', $request->to);
        }

        // Apply clinic filter
        if ($request->clinic_id) {
            $query->where('clinic_id', $request->clinic_id);
        }

        $appointments = $query->orderBy('appointment_time', 'desc')->get();

        // Generate summary statistics
        $summary = [
            'total' => $appointments->count(),
            'confirmed' => $appointments->where('status', 'confirmed')->count(),
            'pending' => $appointments->where('status', 'pending')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
        ];

        return $this->successResponse([
            'appointments' => $appointments,
            'summary' => $summary,
        ]);
    }

    /**
     * Get revenue report
     *
     * @param Request $request - Date range filters
     * @return \Illuminate\Http\JsonResponse - Revenue report data
     */
    public function revenue(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $query = Invoice::with(['patient.user', 'visit']);

        // Apply date filters
        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->get();

        // Generate summary statistics
        $summary = [
            'total_invoices' => $invoices->count(),
            'total_revenue' => $invoices->where('status', 'paid')->sum('total_amount'),
            'pending_revenue' => $invoices->where('status', 'pending')->sum('total_amount'),
            'paid_invoices' => $invoices->where('status', 'paid')->count(),
            'pending_invoices' => $invoices->where('status', 'pending')->count(),
        ];

        // Revenue by day
        $revenueByDay = $invoices->where('status', 'paid')
            ->groupBy(function ($invoice) {
                return $invoice->created_at->format('Y-m-d');
            })
            ->map(function ($dayInvoices) {
                return $dayInvoices->sum('total_amount');
            })
            ->map(function ($revenue, $date) {
                return [
                    'date' => $date,
                    'revenue' => $revenue,
                ];
            })
            ->values();

        return $this->successResponse([
            'invoices' => $invoices,
            'summary' => $summary,
            'revenue_by_day' => $revenueByDay,
        ]);
    }

    /**
     * Get patients report
     *
     * @param Request $request - Date range filters
     * @return \Illuminate\Http\JsonResponse - Patients report data
     */
    public function patients(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $query = User::where('role', 'patient')->with(['appointments', 'invoices']);

        // Apply date filters (for registration date)
        if ($request->from) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $patients = $query->orderBy('created_at', 'desc')->get();

        // Generate summary statistics
        $summary = [
            'total_patients' => $patients->count(),
            'new_this_month' => $patients->where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
            'active_patients' => $patients->filter(function ($patient) {
                return $patient->appointments->where('appointment_time', '>=', Carbon::now()->subMonths(3))->count() > 0;
            })->count(),
        ];

        return $this->successResponse([
            'patients' => $patients,
            'summary' => $summary,
        ]);
    }

    /**
     * Get doctors report
     *
     * @param Request $request - Date range filters
     * @return \Illuminate\Http\JsonResponse - Doctors report data
     */
    public function doctors(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $query = User::where('role', 'doctor')->with(['appointments', 'visits']);

        $doctors = $query->orderBy('created_at', 'desc')->get();

        // Generate statistics for each doctor
        $doctorsWithStats = $doctors->map(function ($doctor) use ($request) {
            $appointmentsQuery = $doctor->appointments();
            
            if ($request->from) {
                $appointmentsQuery->whereDate('appointment_time', '>=', $request->from);
            }
            if ($request->to) {
                $appointmentsQuery->whereDate('appointment_time', '<=', $request->to);
            }

            $appointments = $appointmentsQuery->get();

            return [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'email' => $doctor->email,
                'total_appointments' => $appointments->count(),
                'completed_appointments' => $appointments->where('status', 'completed')->count(),
                'cancelled_appointments' => $appointments->where('status', 'cancelled')->count(),
                'total_visits' => $doctor->visits->count(),
            ];
        });

        return $this->successResponse([
            'doctors' => $doctorsWithStats,
        ]);
    }
}

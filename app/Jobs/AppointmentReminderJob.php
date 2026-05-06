<?php

namespace App\Jobs;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Notification;
use App\Mail\AppointmentReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AppointmentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get appointments in the next 24 hours
        $tomorrow = Carbon::now()->addDay()->endOfDay();
        $inTwoHours = Carbon::now()->addHours(2)->addMinutes(30); // Add 30 minutes buffer
        
        // Find appointments that need reminders
        $appointments = Appointment::where('status', 'confirmed')
            ->where(function($query) use ($tomorrow, $inTwoHours) {
                // 24 hour reminder
                $query->whereBetween('scheduled_at', [
                    Carbon::now()->addHours(23)->startOfMinute(),
                    Carbon::now()->addHours(25)->endOfMinute()
                ])
                // 2 hour reminder
                ->orWhereBetween('scheduled_at', [
                    Carbon::now()->addHours(1)->startOfMinute(),
                    Carbon::now()->addHours(3)->endOfMinute()
                ]);
            })
            ->with(['patient', 'doctor.user', 'clinic'])
            ->get();

        foreach ($appointments as $appointment) {
            $this->sendReminder($appointment);
        }
    }

    private function sendReminder(Appointment $appointment): void
    {
        $now = Carbon::now();
        $appointmentTime = Carbon::parse($appointment->scheduled_at);
        $hoursUntil = $now->diffInHours($appointmentTime, false);

        // Determine reminder type and priority
        if ($hoursUntil <= 3 && $hoursUntil >= 1) {
            // 2 hour reminder
            $title = 'تذكير بموعدك بعد ساعتين';
            $message = "موعدك مع د. {$appointment->doctor->user->name} بعد {$hoursUntil} ساعات";
            $priority = NotificationPriority::HIGH;
        } elseif ($hoursUntil <= 25 && $hoursUntil >= 23) {
            // 24 hour reminder
            $title = 'تذكير بموعدك غداً';
            $message = "موعدك مع د. {$appointment->doctor->user->name} غداً في {$appointmentTime->format('h:i A')}";
            $priority = NotificationPriority::MEDIUM;
        } else {
            return; // Don't send if outside reminder windows
        }

        // Send notification to patient
        $this->createNotification(
            $appointment->patient_id,
            $title,
            $message,
            NotificationType::APPOINTMENT_REMINDER,
            $priority,
            '/appointments/' . $appointment->id,
            ['appointment_id' => $appointment->id]
        );

        // Send email to patient
        $patient = User::find($appointment->patient_id);
        if ($patient && $patient->email) {
            Mail::to($patient->email)
                ->queue(new AppointmentReminderMail($appointment, $hoursUntil));
        }

        // Send notification to doctor
        $this->createNotification(
            $appointment->doctor->user_id,
            'موعد مع مريض',
            "موعد مع {$patient->name} بعد {$hoursUntil} ساعات",
            NotificationType::APPOINTMENT_REMINDER,
            NotificationPriority::MEDIUM,
            '/appointments/' . $appointment->id,
            ['appointment_id' => $appointment->id]
        );
    }

    private function createNotification(
        int $userId,
        string $title,
        string $message,
        NotificationType $type,
        NotificationPriority $priority,
        ?string $link = null,
        array $data = []
    ): void {
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'priority' => $priority,
            'link' => $link,
            'data' => $data,
        ]);
    }
}

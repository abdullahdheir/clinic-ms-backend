<?php

namespace App\Jobs;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\Appointment;
use App\Models\Notification;
use App\Mail\AppointmentConfirmedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class AppointmentConfirmedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The appointment instance.
     *
     * @var Appointment
     */
    public $appointment;

    /**
     * Create a new job instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Load relationships
        $this->appointment->load(['patient.user', 'doctor.user', 'clinic']);

        // Send notification to patient
        $this->createNotification(
            $this->appointment->patient->user_id,
            'تم تأكيد موعدك',
            "تم تأكيد موعدك مع د. {$this->appointment->doctor->user->name} في {$this->appointment->appointment_time->format('Y-m-d h:i A')}",
            NotificationType::APPOINTMENT_CONFIRMED,
            NotificationPriority::MEDIUM,
            '/appointments/' . $this->appointment->id,
            ['appointment_id' => $this->appointment->id]
        );

        // Send email to patient
        if ($this->appointment->patient->user->email) {
            Mail::to($this->appointment->patient->user->email)
                ->queue(new AppointmentConfirmedMail($this->appointment));
        }

        // Send notification to doctor
        $this->createNotification(
            $this->appointment->doctor->user_id,
            'موعد جديد مؤكد',
            "موعد جديد مع {$this->appointment->patient->user->name} في {$this->appointment->appointment_time->format('Y-m-d h:i A')}",
            NotificationType::APPOINTMENT_CONFIRMED,
            NotificationPriority::MEDIUM,
            '/appointments/' . $this->appointment->id,
            ['appointment_id' => $this->appointment->id]
        );

        // Send notification to receptionists (users with receptionist role)
        $this->notifyReceptionists();
    }

    private function notifyReceptionists(): void
    {
        $receptionists = \App\Models\User::where('role', 'receptionist')->get();

        foreach ($receptionists as $receptionist) {
            $this->createNotification(
                $receptionist->id,
                'موعد تم تأكيده',
                "تم تأكيد موعد {$this->appointment->patient->user->name} مع د. {$this->appointment->doctor->user->name}",
                NotificationType::APPOINTMENT_CONFIRMED,
                NotificationPriority::LOW,
                '/appointments/' . $this->appointment->id,
                ['appointment_id' => $this->appointment->id]
            );
        }
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

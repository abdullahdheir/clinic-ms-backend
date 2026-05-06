<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The appointment instance.
     *
     * @var Appointment
     */
    public $appointment;

    /**
     * The number of hours until the appointment.
     *
     * @var int
     */
    public $hoursUntil;

    /**
     * Create a new message instance.
     */
    public function __construct(Appointment $appointment, int $hoursUntil)
    {
        $this->appointment = $appointment;
        $this->hoursUntil = $hoursUntil;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->hoursUntil <= 3 
            ? 'تذكير بموعدك بعد ساعتين - عيادة الطب النبوي' 
            : 'تذكير بموعدك غداً - عيادة الطب النبوي';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.appointments.reminder',
            with: [
                'appointment' => $this->appointment,
                'hoursUntil' => $this->hoursUntil,
                'patientName' => $this->appointment->patient->user->name,
                'doctorName' => $this->appointment->doctor->user->name,
                'clinicName' => $this->appointment->clinic->name,
                'appointmentTime' => $this->appointment->appointment_time->format('Y-m-d h:i A'),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

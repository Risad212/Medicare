<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\LabOrder;
use App\Models\Prescription;
use App\Models\User;
use App\Notifications\AppointmentBookedPatient;
use App\Notifications\AppointmentReminderPatient;
use App\Notifications\AppointmentStatusChangedPatient;
use App\Notifications\LabResultReadyPatient;
use App\Notifications\PrescriptionReadyPatient;
use Illuminate\Notifications\Notification;

class PatientNotifier
{
    /**
     * Notify the patient user connected to an appointment that a booking landed.
     */
    public static function appointmentBooked(Appointment $appointment): void
    {
        self::send($appointment->user, new AppointmentBookedPatient($appointment));
    }

    /**
     * Notify the patient user that one of their appointments changed status.
     */
    public static function appointmentStatusChanged(Appointment $appointment, int $status): void
    {
        self::send($appointment->user, new AppointmentStatusChangedPatient($appointment, $status));
    }

    /**
     * Notify the patient user about an upcoming appointment (reminder command).
     */
    public static function appointmentReminder(Appointment $appointment): void
    {
        self::send($appointment->user, new AppointmentReminderPatient($appointment));
    }

    /**
     * Notify the patient user that lab results for their order are ready.
     */
    public static function labResultReady(LabOrder $order): void
    {
        self::send($order->user, new LabResultReadyPatient($order));
    }

    /**
     * Notify the patient user that a prescription was written for them.
     */
    public static function prescriptionReady(Prescription $prescription): void
    {
        self::send($prescription->patient, new PrescriptionReadyPatient($prescription));
    }

    /**
     * In-app notifications only ever reach users with an account.
     * Guests still get reach via email in the calling flows.
     */
    private static function send(?User $user, Notification $notification): void
    {
        if ($user) {
            $user->notify($notification);
        }
    }
}

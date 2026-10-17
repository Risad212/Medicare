<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\User;
use App\Modules\Lab\Models\LabOrder;
use App\Notifications\AppointmentBookedPatient;
use App\Notifications\AppointmentReminderPatient;
use App\Notifications\AppointmentStatusChangedPatient;
use App\Notifications\LabResultReadyPatient;
use App\Notifications\PrescriptionReadyPatient;
use App\Notifications\PrescriptionWithdrawnPatient;
use App\Services\Sms\SmsNotifier;
use Illuminate\Notifications\Notification;

class PatientNotifier
{
    /**
     * Notify the patient that a booking landed (in-app + SMS).
     */
    public static function appointmentBooked(Appointment $appointment): void
    {
        self::send($appointment->user, new AppointmentBookedPatient($appointment));
        SmsNotifier::appointmentBooked($appointment);
    }

    /**
     * Notify the patient that an appointment changed status (in-app + SMS).
     */
    public static function appointmentStatusChanged(Appointment $appointment, int $status): void
    {
        self::send($appointment->user, new AppointmentStatusChangedPatient($appointment, $status));
        SmsNotifier::appointmentStatusChanged($appointment, $status);
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
     * Notify the patient user that a prescription was withdrawn by the doctor.
     */
    public static function prescriptionWithdrawn(Prescription $prescription): void
    {
        self::send($prescription->patient, new PrescriptionWithdrawnPatient($prescription));
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

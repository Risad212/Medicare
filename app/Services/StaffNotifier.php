<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\LabOrder;
use App\Models\User;
use App\Notifications\AppointmentBooked;
use App\Notifications\AppointmentReminder;
use App\Notifications\AppointmentStatusChanged;
use App\Notifications\LabResultReady;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class StaffNotifier
{
    /**
     * Notify all admins plus the appointment's doctor that a new booking landed.
     */
    public static function appointmentBooked(Appointment $appointment): void
    {
        self::send(self::staffForAppointment($appointment), new AppointmentBooked($appointment));
    }

    /**
     * Notify staff that an appointment's status changed, excluding the acting user.
     */
    public static function appointmentStatusChanged(Appointment $appointment, int $status, ?int $excludeUserId = null): void
    {
        $recipients = self::staffForAppointment($appointment)
            ->reject(fn (User $user) => $user->getKey() === $excludeUserId);

        self::send($recipients, new AppointmentStatusChanged($appointment, $status));
    }

    /**
     * Notify the appointment's doctor about an upcoming visit (reminder command).
     */
    public static function appointmentReminder(Appointment $appointment): void
    {
        if ($appointment->doctor?->user_id) {
            self::send(User::whereKey([$appointment->doctor->user_id])->get(), new AppointmentReminder($appointment));
        }
    }

    /**
     * Notify admins plus the order's doctor that lab results are ready.
     */
    public static function labResultReady(LabOrder $order, ?int $excludeUserId = null): void
    {
        $recipients = self::staffForLabOrder($order)
            ->reject(fn (User $user) => $user->getKey() === $excludeUserId);

        self::send($recipients, new LabResultReady($order));
    }

    /**
     * Resolve staff recipients for an appointment: every admin + the linked
     * doctor's user (guests with no account skip the patient side entirely).
     */
    private static function staffForAppointment(Appointment $appointment): Collection
    {
        $ids = User::where('role', 'admin')->pluck('id');

        if ($appointment->doctor?->user_id) {
            $ids->push($appointment->doctor->user_id);
        }

        return User::whereKey($ids->unique()->values())->get();
    }

    /**
     * Resolve staff recipients for a lab order: every admin + the requesting doctor.
     */
    private static function staffForLabOrder(LabOrder $order): Collection
    {
        $ids = User::where('role', 'admin')->pluck('id');

        if ($order->doctor?->user_id) {
            $ids->push($order->doctor->user_id);
        }

        return User::whereKey($ids->unique()->values())->get();
    }

    private static function send(Collection $recipients, Notification $notification): void
    {
        if ($recipients->isNotEmpty()) {
            NotificationFacade::send($recipients, $notification);
        }
    }
}

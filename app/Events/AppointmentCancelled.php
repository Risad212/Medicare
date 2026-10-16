<?php

namespace App\Events;

use App\Models\Appointment;

class AppointmentCancelled
{
    public function __construct(public readonly Appointment $appointment) {}
}

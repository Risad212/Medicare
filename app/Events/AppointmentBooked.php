<?php

namespace App\Events;

use App\Models\Appointment;

class AppointmentBooked
{
    public function __construct(public readonly Appointment $appointment) {}
}

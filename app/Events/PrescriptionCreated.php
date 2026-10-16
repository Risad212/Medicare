<?php

namespace App\Events;

use App\Models\Prescription;

class PrescriptionCreated
{
    public function __construct(public readonly Prescription $prescription) {}
}

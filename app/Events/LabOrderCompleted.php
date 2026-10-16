<?php

namespace App\Events;

class LabOrderCompleted
{
    public function __construct(public readonly int $labOrderId) {}
}

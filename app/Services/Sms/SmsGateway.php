<?php

namespace App\Services\Sms;

interface SmsGateway
{
    /**
     * Deliver one SMS. Implementations throw SmsDeliveryException on failure.
     *
     * @throws SmsDeliveryException
     */
    public function send(string $to, string $message): void;
}

<?php

namespace Tests\Support;

use App\Services\Sms\SmsGateway;

class FakeSmsGateway implements SmsGateway
{
    /** @var array<int, array{to: string, message: string}> */
    public array $messages = [];

    public function send(string $to, string $message): void
    {
        $this->messages[] = ['to' => $to, 'message' => $message];
    }

    /**
     * @return array<int, array{to: string, message: string}>
     */
    public function messagesFor(string $to): array
    {
        return array_values(array_filter($this->messages, fn (array $m) => $m['to'] === $to));
    }

    public function lastMessage(): ?array
    {
        return $this->messages === [] ? null : $this->messages[array_key_last($this->messages)];
    }
}

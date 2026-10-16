<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_responses_include_a_generated_request_id(): void
    {
        $response = $this->get('/up', ['X-Request-ID' => 'caller-controlled-value']);

        $response->assertOk();

        $requestId = $response->headers->get('X-Request-ID');
        $this->assertNotSame('caller-controlled-value', $requestId);
        $this->assertNotFalse($requestId);
        $this->assertSame(36, strlen($requestId));
    }

    public function test_exception_responses_include_the_request_id(): void
    {
        $response = $this->get('/missing-route-for-request-id-test');

        $response->assertNotFound();
        $this->assertSame(36, strlen((string) $response->headers->get('X-Request-ID')));
    }
}

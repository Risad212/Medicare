<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Blade layouts use @vite(); CI has no public/build manifest,
        // so stub Vite in every test to avoid 500s on page renders.
        $this->withoutVite();
    }
}

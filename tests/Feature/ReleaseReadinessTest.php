<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ReleaseReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_release_readiness_reports_every_check_area(): void
    {
        $exit = Artisan::call('release:readiness');
        $output = Artisan::output();

        $this->assertSame(0, $exit);

        foreach (['permissions', 'environment', 'modules', 'translations', 'credentials', 'queue', 'zoom-recording', 'integrations', 'logging', 'backups', 'migrations'] as $check) {
            $this->assertStringContainsString($check, $output);
        }
    }

    public function test_env_example_ships_with_debug_disabled(): void
    {
        $example = file_get_contents(base_path('.env.example'));

        $this->assertMatchesRegularExpression('/^APP_DEBUG=false$/m', $example);
    }

    public function test_missing_locale_file_fails_the_gate(): void
    {
        $path = base_path('lang/pt/messages.php');
        $backup = base_path('lang/pt/messages.php.release_test_backup');
        rename($path, $backup);

        try {
            $exit = Artisan::call('release:readiness');
            $output = Artisan::output();
        } finally {
            rename($backup, $path);
        }

        $this->assertFileExists($path);
        $this->assertSame(1, $exit);
        $this->assertStringContainsString('translations', $output);
        $this->assertStringContainsString('FAIL', $output);
    }
}

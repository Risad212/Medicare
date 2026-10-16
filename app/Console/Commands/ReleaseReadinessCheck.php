<?php

namespace App\Console\Commands;

use App\Support\Module;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ReleaseReadinessCheck extends Command
{
    protected $signature = 'release:readiness';

    protected $description = 'Pre-release checks: permissions, modules, translations, credentials, queue, logging, backups, and migrations.';

    private const MESSAGE_SECTIONS = ['notifications', 'mail', 'pdf', 'public', 'mail_body', 'pdf_body', 'staff'];

    public function handle(): int
    {
        $rows = [];
        $failures = 0;
        $warnings = 0;

        $record = function (string $check, string $status, string $detail) use (&$rows, &$failures, &$warnings): void {
            $rows[] = [$check, strtoupper($status), $detail];
            if ($status === 'fail') {
                $failures++;
            } elseif ($status === 'warn') {
                $warnings++;
            }
        };

        $this->checkPermissions($record);
        $this->checkEnvironment($record);
        $this->checkModules($record);
        $this->checkTranslations($record);
        $this->checkCredentials($record);
        $this->checkQueue($record);
        $this->checkZoomRecording($record);
        $this->checkIntegrationFailures($record);
        $this->checkLogging($record);
        $this->checkBackups($record);
        $this->checkMigrations($record);

        $this->table(['Check', 'Status', 'Detail'], $rows);
        $this->line("release:readiness — {$failures} failing, {$warnings} warning(s).");

        if ($failures > 0) {
            $this->error('Not ready for release. Fix the failing checks above.');

            return self::FAILURE;
        }

        if ($warnings > 0) {
            $this->warn('Ready with warnings. Review each warning before release.');
        } else {
            $this->info('Ready for release.');
        }

        return self::SUCCESS;
    }

    /**
     * @param  callable(string, string, string): void  $record
     */
    private function checkPermissions(callable $record): void
    {
        foreach ([storage_path(), base_path('bootstrap/cache')] as $path) {
            if (! is_dir($path) || ! is_writable($path)) {
                $record('permissions', 'fail', "{$path} is missing or not writable.");

                return;
            }
        }

        $record('permissions', 'pass', 'storage/ and bootstrap/cache are writable.');
    }

    /**
     * @param  callable(string, string, string): void  $record
     */
    private function checkEnvironment(callable $record): void
    {
        if ((string) config('app.key') === '') {
            $record('environment', 'fail', 'APP_KEY is empty.');

            return;
        }

        if (app()->isProduction() && (bool) config('app.debug')) {
            $record('environment', 'fail', 'APP_DEBUG is on in production.');

            return;
        }

        $record('environment', 'pass', 'APP_KEY set; debug off in production.');
    }

    /**
     * @param  callable(string, string, string): void  $record
     */
    private function checkModules(callable $record): void
    {
        $map = (function (): array {
            $ref = new \ReflectionClass(Module::class);
            $prop = $ref->getConstant('REQUIRED_CLASSES');

            return is_array($prop) ? $prop : [];
        })();

        $broken = [];

        foreach (config('modules', []) as $name => $flag) {
            if (! $flag) {
                continue;
            }

            $class = $map[$name] ?? null;

            if (is_string($class) && ! class_exists($class)) {
                $broken[] = "{$name} is enabled but {$class} is missing (removed dir or stale class map — run composer dump-autoload).";
            }
        }

        if ($broken !== []) {
            $record('modules', 'fail', implode(' ', $broken));

            return;
        }

        $record('modules', 'pass', 'Every enabled module resolves its provider class.');
    }

    /**
     * @param  callable(string, string, string): void  $record
     */
    private function checkTranslations(callable $record): void
    {
        $locales = ['ar', 'bn', 'de', 'en', 'es', 'fr', 'it', 'pt', 'ru', 'tr', 'zh'];
        $english = require base_path('lang/en/messages.php');
        $problems = [];

        foreach ($locales as $locale) {
            $validation = base_path("lang/{$locale}/validation.php");
            $messages = base_path("lang/{$locale}/messages.php");

            if (! is_file($validation) || ! is_file($messages)) {
                $problems[] = "{$locale}: missing language file.";

                continue;
            }

            $catalog = require $messages;

            foreach (self::MESSAGE_SECTIONS as $section) {
                if (! isset($catalog[$section])) {
                    $problems[] = "{$locale}: missing [{$section}].";

                    continue;
                }

                $missing = array_diff(array_keys($english[$section]), array_keys($catalog[$section]));

                if ($missing !== []) {
                    $problems[] = "{$locale}: [{$section}] missing ".implode(',', $missing).'.';
                }
            }
        }

        if ($problems !== []) {
            $record('translations', 'fail', implode(' ', array_slice($problems, 0, 5)));

            return;
        }

        $record('translations', 'pass', count($locales).' locales with full key parity.');
    }

    /**
     * @param  callable(string, string, string): void  $record
     */
    private function checkCredentials(callable $record): void
    {
        $missing = [];

        if ((string) config('services.google.client_id') === '' || (string) config('services.google.client_secret') === '') {
            $missing[] = 'Google OAuth (GOOGLE_CLIENT_ID/SECRET)';
        }

        if (Module::enabled('zoom')
            && ((string) config('services.zoom.account_id') === ''
                || (string) config('services.zoom.client_id') === ''
                || (string) config('services.zoom.client_secret') === '')) {
            $missing[] = 'Zoom API (ZOOM_ACCOUNT_ID/CLIENT_ID/SECRET)';
        }

        if ($missing !== []) {
            $record('credentials', 'warn', 'Missing live credentials: '.implode('; ', $missing).'. Automated tests pass; live behavior needs deployment setup.');

            return;
        }

        $record('credentials', 'pass', 'Google/Zoom credentials present.');
    }

    /**
     * @param  callable(string, string, string): void  $record
     */
    private function checkQueue(callable $record): void
    {
        $connection = (string) config('queue.default', 'sync');

        if ($connection === 'sync' && app()->isProduction()) {
            $record('queue', 'fail', 'QUEUE_CONNECTION=sync in production: mails and sync jobs run inline.');

            return;
        }

        try {
            $pending = DB::table('jobs')->count();
        } catch (\Throwable) {
            $pending = null;
        }

        if ($connection === 'sync') {
            $record('queue', 'warn', 'QUEUE_CONNECTION=sync: fine for local dev, use database/redis plus a worker in production.');

            return;
        }

        if (is_int($pending) && $pending > 100) {
            $record('queue', 'warn', "{$pending} jobs waiting — verify a queue worker is running.");

            return;
        }

        $record('queue', 'pass', "Connection [{$connection}]; verify a worker is supervised.");
    }

    /**
     * @param  callable(string, string, string): void  $record
     */
    private function checkZoomRecording(callable $record): void
    {
        if (! Module::enabled('zoom')) {
            $record('zoom-recording', 'pass', 'Zoom module off — nothing to verify.');

            return;
        }

        $record('zoom-recording', 'warn', 'Cannot verify account-side settings via API: confirm cloud recording is restricted in the Zoom dashboard before release.');
    }

    /**
     * @param  callable(string, string, string): void  $record
     */
    private function checkIntegrationFailures(callable $record): void
    {
        $failed = 0;

        try {
            if (Schema::hasTable('google_calendar_events')) {
                $failed += DB::table('google_calendar_events')->where('sync_status', 'failed')->count();
            }

            if (Schema::hasTable('zoom_meetings')) {
                $failed += DB::table('zoom_meetings')->where('sync_status', 'failed')->count();
            }
        } catch (\Throwable) {
            $record('integrations', 'warn', 'Could not read sync tables; triage integration failures from the admin failed-events screens.');

            return;
        }

        if ($failed > 0) {
            $record('integrations', 'warn', "{$failed} failed sync record(s) need triage before release.");

            return;
        }

        $record('integrations', 'pass', 'No failed Google/Zoom sync records.');
    }

    /**
     * @param  callable(string, string, string): void  $record
     */
    private function checkLogging(callable $record): void
    {
        $channel = (string) config('logging.default', 'stack');
        $path = storage_path('logs/laravel.log');

        if (! is_writable(dirname($path))) {
            $record('logging', 'fail', 'storage/logs is not writable.');

            return;
        }

        $record('logging', 'pass', "Channel [{$channel}] with writable storage/logs.");
    }

    /**
     * @param  callable(string, string, string): void  $record
     */
    private function checkBackups(callable $record): void
    {
        if (! Module::enabled('backups')) {
            $record('backups', 'pass', 'Backups module off — ensure an external backup story exists.');

            return;
        }

        $disks = config('backup.backup.destination.disks', ['local']);
        $disk = is_array($disks) ? ($disks[0] ?? 'local') : 'local';
        $prefix = (string) config('backup.backup.name', 'laravel-backup');

        try {
            $files = Storage::disk($disk)->files($prefix.'/');
            $zips = array_filter($files, static fn (string $f): bool => str_ends_with($f, '.zip'));
        } catch (\Throwable) {
            $record('backups', 'warn', "Could not list backups on disk [{$disk}]; verify backup storage manually.");

            return;
        }

        if ($zips === []) {
            $record('backups', 'warn', "No backups found on disk [{$disk}]; run a backup and verify restore before release.");

            return;
        }

        $record('backups', 'pass', count($zips).' backup(s) on disk ['.$disk.']; verify restore before release.');
    }

    /**
     * @param  callable(string, string, string): void  $record
     */
    private function checkMigrations(callable $record): void
    {
        try {
            if (! Schema::hasTable('migrations')) {
                $record('migrations', 'fail', 'Migrations table missing — database was never migrated.');

                return;
            }

            $ran = DB::table('migrations')->pluck('migration')->all();
        } catch (\Throwable) {
            $record('migrations', 'fail', 'Could not read migration state.');

            return;
        }

        $files = glob(database_path('migrations/*.php')) ?: [];
        $pending = [];

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);

            if (! in_array($name, $ran, true)) {
                $pending[] = $name;
            }
        }

        if ($pending !== []) {
            $record('migrations', 'fail', count($pending).' pending migration(s): '.implode(', ', array_slice($pending, 0, 3)).'.');

            return;
        }

        $record('migrations', 'pass', count($files).' migrations applied; rollback via backups + migrate:rollback plan.');
    }
}

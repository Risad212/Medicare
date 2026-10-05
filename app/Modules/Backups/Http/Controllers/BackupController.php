<?php

namespace App\Modules\Backups\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\AdminNavigation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BackupController extends Controller
{
    public function index(): Response
    {
        $backups = $this->listBackups();

        return Inertia::render('Admin/Backups/Index', [
            'backups' => array_map(fn (array $backup) => [
                ...$backup,
                'createdAt' => Carbon::createFromTimestamp($backup['created_at'])->format('d M Y h:i A'),
                'downloadUrl' => route('admin.backups.download', $backup['name']),
            ], $backups),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.backups.index'),
                'run' => route('admin.backups.run'),
            ],
        ]);
    }

    public function download(string $file)
    {
        // Strict allowlist: plain zip filename from our own listing, so
        // '../../.env'-style traversal can never escape the backup dir.
        abort_unless($this->isListedBackup($file), 404);

        return Storage::disk($this->disk())->download($this->prefix().$file);
    }

    public function run(): RedirectResponse
    {
        $exitCode = Artisan::call('backup:run', ['--disable-notifications' => true]);

        if ($exitCode !== 0) {
            return back()->with('error', 'Backup failed. Check the application logs for details.');
        }

        return back()->with('success', 'Backup completed successfully.');
    }

    private function disk(): string
    {
        return (string) (config('backup.backup.destination.disks')[0] ?? 'local');
    }

    private function prefix(): string
    {
        return (string) config('backup.backup.name', 'laravel-backup').'/';
    }

    /**
     * @return array<int, array{name: string, size: int, created_at: int}>
     */
    private function listBackups(): array
    {
        $disk = Storage::disk($this->disk());
        $prefix = $this->prefix();

        if (! $disk->exists($prefix)) {
            return [];
        }

        $backups = [];

        foreach ($disk->files($prefix) as $path) {
            if (! str_ends_with($path, '.zip')) {
                continue;
            }

            $backups[] = [
                'name' => basename($path),
                'size' => $disk->size($path),
                'created_at' => $disk->lastModified($path),
            ];
        }

        usort($backups, fn ($a, $b) => $b['created_at'] <=> $a['created_at']);

        return $backups;
    }

    private function isListedBackup(string $file): bool
    {
        if ($file === '' || basename($file) !== $file || ! str_ends_with($file, '.zip')) {
            return false;
        }

        foreach ($this->listBackups() as $backup) {
            if ($backup['name'] === $file) {
                return true;
            }
        }

        return false;
    }
}

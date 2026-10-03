<?php

namespace App\Modules\Backups\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        return view('backups.index', ['backups' => $this->listBackups()]);
    }

    public function download(string $file)
    {
        // Strict allowlist: plain zip filename from our own listing, so
        // '../../.env'-style traversal can never escape the backup dir.
        abort_unless($this->isListedBackup($file), 404);

        return Storage::disk($this->disk())->download($this->prefix().$file);
    }

    public function run()
    {
        Artisan::call('backup:run', ['--disable-notifications' => true]);

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

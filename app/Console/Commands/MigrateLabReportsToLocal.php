<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateLabReportsToLocal extends Command
{
    protected $signature = 'reports:migrate-to-local';

    protected $description = 'Move legacy lab report files from the public disk to the private local disk (H1 PHI fix). Run once after deploy.';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $local = Storage::disk('local');

        if (! $public->exists('lab_reports')) {
            $this->info('Nothing to migrate: no lab_reports directory on the public disk.');

            return self::SUCCESS;
        }

        $moved = 0;

        foreach ($public->allFiles('lab_reports') as $path) {
            if (! $local->exists($path)) {
                $local->put($path, $public->get($path));
            }
            $public->delete($path);
            $moved++;
        }

        $this->info("Migrated {$moved} report file(s) to the local disk.");

        return self::SUCCESS;
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Keep test backups tiny: one file instead of the whole project.
        config(['backup.backup.source.files.include' => [base_path('composer.json')]]);
        config(['backup.backup.source.files.exclude' => []]);

        // Start from a clean slate (dev backups may exist on disk).
        $prefix = (string) config('backup.backup.name').'/';
        Storage::disk('local')->delete(Storage::disk('local')->files($prefix));
    }

    public function test_guest_cannot_access_backups(): void
    {
        $this->get('/admin/backups')->assertRedirect('/login');
    }

    public function test_admin_sees_empty_state_before_first_backup(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/backups')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Backups/Index')
                ->where('backups', [])
                ->has('routes.run')
            );
    }

    public function test_admin_can_run_backup_and_download_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/backups/run')
            ->assertRedirect()
            ->assertSessionHas('success');

        $disk = Storage::disk('local');
        $zips = array_values(array_filter(
            $disk->files((string) config('backup.backup.name').'/'),
            fn ($path) => str_ends_with($path, '.zip')
        ));
        $this->assertNotEmpty($zips);

        $name = basename($zips[0]);
        $response = $this->actingAs($admin)->get("/admin/backups/download/{$name}");
        $response->assertOk();
        $this->assertSame($disk->get($zips[0]), $response->streamedContent());

        $disk->delete($zips);
    }

    public function test_download_rejects_traversal_and_unknown_files(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/backups/download/..%2F..%2F.env')->assertNotFound();
        $this->actingAs($admin)->get('/admin/backups/download/nope.zip')->assertNotFound();
        $this->actingAs($admin)->get('/admin/backups/download/evil.php')->assertNotFound();
    }

    public function test_non_admin_staff_cannot_access_backups(): void
    {
        $receptionist = User::factory()->create(['role' => 'receptionist']);

        $this->actingAs($receptionist)->get('/admin/backups')->assertForbidden();
    }

    public function test_disabled_module_hides_backup_routes(): void
    {
        config(['modules.backups' => false]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/backups')->assertNotFound();
        $this->actingAs($admin)->post('/admin/backups/run')->assertNotFound();
    }
}

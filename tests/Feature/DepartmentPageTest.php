<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_page_lists_active_doctors_of_that_department(): void
    {
        $listed = $this->makeDoctor('Heart Doctor', 'Cardiology', 1);
        $this->makeDoctor('Retired Heart Doctor', 'Cardiology', 0);
        $this->makeDoctor('Brain Doctor', 'Neurology', 1);

        $this->get(route('department.show', ['name' => 'Cardiology']))
            ->assertOk()
            ->assertSee('hospital-doctors', false)
            ->assertSee('Doctors in this department')
            ->assertSee($listed->name)
            ->assertSee('appointment?doctor_id='.$listed->id, false)
            ->assertDontSee('Retired Heart Doctor')
            ->assertDontSee('Brain Doctor');
    }

    public function test_department_page_lists_related_services_from_same_department(): void
    {
        $this->makeDoctor('Heart Doctor', 'Cardiology', 1);
        $service = $this->makeService(['title' => 'Echo Test Service', 'department' => 'Cardiology']);
        $this->makeService(['title' => 'Skin Test Service', 'department' => 'Dermatology']);

        $this->get(route('department.show', ['name' => 'Cardiology']))
            ->assertOk()
            ->assertSee('Related services')
            ->assertSee($service->title)
            ->assertDontSee('Skin Test Service');
    }

    public function test_department_page_hides_service_section_when_no_services_match(): void
    {
        $this->makeDoctor('Heart Doctor', 'Cardiology', 1);

        $this->get(route('department.show', ['name' => 'Cardiology']))
            ->assertOk()
            ->assertDontSee('Related services');
    }

    public function test_department_page_shows_empty_message_when_only_services_match(): void
    {
        $this->makeService(['title' => 'Echo Test Service', 'department' => 'Cardiology']);
        $this->makeDoctor('Retired Heart Doctor', 'Cardiology', 0);

        $this->get(route('department.show', ['name' => 'Cardiology']))
            ->assertOk()
            ->assertSee('No doctors to show right now.')
            ->assertDontSee('Retired Heart Doctor');
    }

    public function test_department_page_is_404_when_nothing_matches(): void
    {
        $this->get(route('department.show', ['name' => 'Ghostology']))->assertNotFound();
        $this->get(route('department.show', ['name' => '   ']))->assertNotFound();
    }

    public function test_service_detail_links_to_the_department_page(): void
    {
        $service = $this->makeService(['department' => 'Cardiology']);
        $this->makeDoctor('Heart Doctor', 'Cardiology', 1);

        $this->get(route('service.show', $service))
            ->assertOk()
            ->assertSee('/department/Cardiology', false)
            ->assertSee('>View all<', false);
    }

    public function test_department_page_renders_one_h1_from_the_department_name(): void
    {
        $this->makeDoctor('Heart Doctor', 'Cardiology', 1);

        $html = (string) $this->get(route('department.show', ['name' => 'Cardiology']))
            ->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('<h1 class="page-name">Cardiology</h1>', $html);
    }

    public function test_department_page_uses_the_stored_department_description_as_meta(): void
    {
        Department::create([
            'name' => 'Cardiology',
            'description' => 'Heart care centre description for testing.',
            'status' => 1,
        ]);
        $this->makeDoctor('Heart Doctor', 'Cardiology', 1);

        $this->get(route('department.show', ['name' => 'Cardiology']))
            ->assertOk()
            ->assertSee('Heart care centre description for testing.', false);
    }

    private function makeService(array $overrides): Service
    {
        return Service::create(array_merge([
            'title' => 'Heart Care '.uniqid(),
            'slug' => 'heart-care-'.uniqid(),
            'status' => 1,
        ], $overrides));
    }

    private function makeDoctor(string $name, ?string $department, int $status): Doctor
    {
        $user = User::factory()->create(['role' => 'doctor']);

        return Doctor::create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)).'-'.uniqid(),
            'department' => $department,
            'user_id' => $user->id,
            'status' => $status,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceDoctorNavigationTest extends TestCase
{
    use RefreshDatabase;

    /** T5: only active doctors of the linked department are listed. */
    public function test_service_with_department_lists_matching_active_doctors(): void
    {
        $service = $this->makeService(['department' => 'Cardiology']);
        $listed = $this->makeDoctor('Heart Doctor', 'Cardiology', 1);
        $this->makeDoctor('Retired Heart Doctor', 'Cardiology', 0);
        $this->makeDoctor('Brain Doctor', 'Neurology', 1);

        $response = $this->get(route('service.show', $service))->assertOk();

        $response->assertSee('hospital-doctors', false);
        $response->assertSee('Doctors in this department');
        $response->assertSee($listed->name);
        $response->assertDontSee('Retired Heart Doctor');
        $response->assertDontSee('Brain Doctor');
    }

    public function test_service_without_department_renders_no_doctor_section(): void
    {
        $service = $this->makeService(['department' => null]);
        $this->makeDoctor('Heart Doctor', 'Cardiology', 1);

        $this->get(route('service.show', $service))
            ->assertOk()
            ->assertDontSee('hospital-doctors', false)
            ->assertDontSee('Doctors in this department');
    }

    public function test_department_with_no_active_doctors_renders_no_doctor_section(): void
    {
        $service = $this->makeService(['department' => 'Cardiology']);
        $this->makeDoctor('Retired Heart Doctor', 'Cardiology', 0);

        $this->get(route('service.show', $service))
            ->assertOk()
            ->assertDontSee('hospital-doctors', false);
    }

    /** T5: per-doctor booking deep-link carries the doctor id preselect. */
    public function test_doctor_book_links_carry_doctor_id_preselect(): void
    {
        $service = $this->makeService(['department' => 'Cardiology']);
        $doctor = $this->makeDoctor('Heart Doctor', 'Cardiology', 1);

        $this->get(route('service.show', $service))
            ->assertOk()
            ->assertSee('appointment?doctor_id='.$doctor->id, false);
    }

    /** T5: the appointment page accepts the deep-linked doctor_id param. */
    public function test_appointment_page_renders_with_doctor_id_param(): void
    {
        $doctor = $this->makeDoctor('Preselected Doctor', 'Cardiology', 1);

        $this->get(route('appointment', ['doctor_id' => $doctor->id]))
            ->assertOk()
            ->assertSee('URLSearchParams', false);
    }

    /** T6: profile facts list renders only populated stored fields. */
    public function test_doctor_profile_shows_structured_facts(): void
    {
        $doctor = $this->makeDoctor('Facts Doctor', 'Cardiology', 1);
        $doctor->update([
            'degree' => 'MBBS, MD (Cardiology)',
            'specialist' => 'Interventional Cardiology',
            'services' => 'Consultation, Follow-up',
            'availability' => 'Everyday 9AM-8PM',
            'phone' => '01712345678',
        ]);

        $this->get(route('doctor.show', $doctor->id))
            ->assertOk()
            ->assertSee('mc-doctor-facts', false)
            ->assertSee('MBBS, MD (Cardiology)')
            ->assertSee('Interventional Cardiology')
            ->assertSee('Everyday 9AM-8PM')
            ->assertSee('<h2 class="doctor-name"', false)
            ->assertSee('<h1 class="page-name"', false);
    }

    /** T6 + plan §1.5: never render invented text for missing fields. */
    public function test_doctor_profile_without_facts_omits_facts_list(): void
    {
        $doctor = $this->makeDoctor('Bare Doctor', null, 1);

        $this->get(route('doctor.show', $doctor->id))
            ->assertOk()
            ->assertDontSee('mc-doctor-facts', false)
            ->assertDontSee('<dt', false);
    }

    /** Acceptance #2 extended to the two detail pages this stage touches. */
    public function test_detail_pages_render_exactly_one_h1(): void
    {
        $service = $this->makeService(['department' => 'Cardiology']);
        $doctor = $this->makeDoctor('Heading Doctor', 'Cardiology', 1);

        foreach ([
            ['service.show', $service],
            ['doctor.show', $doctor],
        ] as [$route, $model]) {
            $html = (string) $this->get(route($route, $model))->assertOk()->getContent();
            $this->assertSame(1, substr_count($html, '<h1'), "Expected one <h1> on [{$route}]");
        }
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

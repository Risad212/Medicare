<?php

namespace Tests\Concerns;

use App\Models\Appointment;
use App\Models\Blog;
use App\Models\Doctor;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesModels
{
    protected function makeUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'patient',
        ], $overrides));
    }

    protected function makeAdmin(): User
    {
        return $this->makeUser(['role' => 'admin']);
    }

    protected function makePatient(array $overrides = []): Patient
    {
        return Patient::create(array_merge([
            'name' => 'Sample Patient',
            'email' => 'patient'.Str::random(6).'@example.com',
            'phone' => '01711-111111',
        ], $overrides));
    }

    protected function makeTimeSlot(array $overrides = []): TimeSlot
    {
        return TimeSlot::create(array_merge([
            'time' => '10:00 AM',
            'status' => 1,
        ], $overrides));
    }

    protected function makeDoctor(array $overrides = []): Doctor
    {
        $user = User::factory()->create(['role' => 'doctor']);

        return Doctor::create(array_merge([
            'name' => 'Dr. Sample',
            'slug' => 'dr-sample-'.Str::random(6),
            'department' => 'Cardiology',
            'specialist' => 'Cardiologist',
            'phone' => '01711-111111',
            'status' => 1,
            'user_id' => $user->id,
        ], $overrides));
    }

    protected function makeBlog(array $overrides = []): Blog
    {
        return Blog::create(array_merge([
            'title' => 'Sample Blog Post',
            'slug' => 'sample-blog-'.Str::random(5),
            'excerpt' => 'Short excerpt',
            'content' => 'Full article content.',
            'author' => 'Admin',
            'status' => 1,
        ], $overrides));
    }

    protected function makeAppointment(array $overrides = []): Appointment
    {
        $doctor = $overrides['doctor_id'] ?? $this->makeDoctor()->id;
        $slot = $overrides['time_slot_id'] ?? $this->makeTimeSlot()->id;

        return Appointment::create(array_merge([
            'doctor_id' => $doctor,
            'time_slot_id' => $slot,
            'patient_name' => 'Alice Patient',
            'age' => 28,
            'gender' => 2,
            'phone' => '01712-222222',
            'email' => 'alice@example.com',
            'cancellation_token' => Str::random(40),
            'visit_type' => 1,
            'appointment_date' => now()->addDay()->toDateString(),
            'status' => 0,
        ], $overrides));
    }

    protected function makeLabTest(array $overrides = []): LabTest
    {
        return LabTest::create(array_merge([
            'name' => 'Complete Blood Count',
            'category' => 'Hematology',
            'price' => 20.50,
            'normal_range' => '4.5-11.0',
            'unit' => 'x10^9/L',
            'status' => true,
        ], $overrides));
    }

    protected function appointmentPayload(array $overrides = []): array
    {
        $doctor = $overrides['doctor_id'] ?? $this->makeDoctor()->id;
        $slot = $overrides['time_slot_id'] ?? $this->makeTimeSlot()->id;

        return array_merge([
            'doctor_id' => $doctor,
            'patient_name' => 'John Doe',
            'age' => 30,
            'gender' => 1,
            'phone' => '01711-223344',
            'email' => 'john@example.com',
            'visit_type' => 2,
            'appointment_date' => now()->addDay()->toDateString(),
            'time_slot_id' => $slot,
        ], $overrides);
    }
}

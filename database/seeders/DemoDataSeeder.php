<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\BloodDonation;
use App\Models\BloodDonor;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\Category;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\GeneralSetting;
use App\Models\Invoice;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabTest;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Service;
use App\Models\Slider;
use App\Models\Tag;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Fill the app with realistic demo content so every module looks alive.
     * Idempotent: re-runs stop after the marker admin account exists.
     */
    public function run(): void
    {
        if (User::where('email', 'admin@medicare.test')->exists()) {
            $this->command->info('Demo data already seeded — skipping.');

            return;
        }

        DB::transaction(function () {
            $this->seedLookups();
            $users = $this->seedUsers();
            $doctors = $this->seedDoctors($users);
            $slots = $this->seedTimeSlots();
            $this->seedContent();
            $appointments = $this->seedAppointments($users, $doctors, $slots);
            $this->seedLaboratory($users, $doctors);
            $this->seedBlood($users, $doctors);
            $this->seedPrescriptions($users, $doctors, $appointments);
            $this->seedSettings();
        });

        $this->command->info('Demo data seeded. Log in as admin@medicare.test / password.');
    }

    private function seedLookups(): void
    {
        foreach (['Cardiology', 'Orthopedics', 'Neurology', 'Pediatrics', 'Dermatology', 'General Medicine'] as $name) {
            Department::firstOrCreate(['name' => $name], ['description' => $name.' department', 'status' => 1]);
        }

        foreach (['09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM', '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM'] as $time) {
            TimeSlot::firstOrCreate(['time' => $time], ['status' => 1]);
        }
    }

    /**
     * @return array{admin: User, staff: User[], patients: User[], doctorUsers: User[]}
     */
    private function seedUsers(): array
    {
        $password = Hash::make('password');

        $admin = User::create([
            'name' => 'Hospital Admin', 'email' => 'admin@medicare.test',
            'password' => $password, 'role' => 'admin', 'email_verified_at' => now(),
        ]);

        $staffMap = [
            ['Front Desk Officer', 'receptionist@medicare.test', 'receptionist', 'receptionist'],
            ['Lab Technologist', 'lab@medicare.test', 'lab-technician', 'lab-technician'],
            ['Duty Pharmacist', 'pharmacist@medicare.test', 'pharmacist', 'pharmacist'],
        ];
        $staff = [];
        foreach ($staffMap as [$name, $email, $role, $slug]) {
            $user = User::create([
                'name' => $name, 'email' => $email, 'password' => $password,
                'role' => $role, 'phone' => '01'.rand(300000000, 399999999), 'email_verified_at' => now(),
            ]);
            $staff[] = $user;
        }

        $patientNames = ['Rahim Uddin', 'Fatema Begum', 'Karim Sheikh', 'Ayesha Siddika', 'Mohan Das', 'Nusrat Jahan', 'Habib Rahman', 'Salma Akter'];
        $patients = [];
        foreach ($patientNames as $i => $name) {
            $patients[] = User::create([
                'name' => $name, 'email' => 'patient'.($i + 1).'@medicare.test',
                'password' => $password, 'role' => 'patient',
                'phone' => '01'.rand(300000000, 399999999),
                'gender' => $i % 2 === 0 ? 'male' : 'female',
                'blood_group' => ['A+', 'B+', 'O+', 'AB+'][$i % 4],
                'address' => 'House '.($i + 3).', Dhaka',
                'email_verified_at' => now(),
            ]);
        }

        $doctorUsers = [];
        for ($i = 1; $i <= 6; $i++) {
            $doctorUsers[] = User::create([
                'name' => 'Doctor '.$i, 'email' => 'doctor'.$i.'@medicare.test',
                'password' => $password, 'role' => 'doctor', 'email_verified_at' => now(),
            ]);
        }

        return compact('admin', 'staff', 'patients', 'doctorUsers');
    }

    private function seedDoctors(array $users): Collection
    {
        $rows = [
            ['Dr. Ayesha Rahman', 'Cardiology', 'Interventional Cardiology', 'MBBS, MD (Cardiology)', 'Everyday 9AM-8PM'],
            ['Dr. Tanvir Hasan', 'Orthopedics', 'Sports Injury', 'MBBS, MS (Ortho)', 'Sat-Thu 10AM-6PM'],
            ['Dr. Nusrat Chowdhury', 'Neurology', 'Stroke & Epilepsy', 'MBBS, MD (Neuro)', 'Sun-Fri 11AM-7PM'],
            ['Dr. Kamal Hossain', 'Pediatrics', 'Neonatology', 'MBBS, DCH', 'Everyday 9AM-5PM'],
            ['Dr. Shabnam Akter', 'Dermatology', 'Cosmetic Dermatology', 'MBBS, DDV', 'Sat-Thu 3PM-9PM'],
            ['Dr. Rafiq Islam', 'General Medicine', 'Internal Medicine', 'MBBS, FCPS', 'Everyday 10AM-8PM'],
        ];

        return collect($rows)->map(function ($row, $i) use ($users) {
            [$name, $department, $specialist, $degree, $availability] = $row;

            return Doctor::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name, 'degree' => $degree, 'department' => $department,
                    'specialist' => $specialist, 'availability' => $availability,
                    'phone' => '01'.rand(700000000, 799999999), 'status' => 1,
                    'user_id' => $users['doctorUsers'][$i]->id,
                    'services' => 'Consultation, Follow-up, Emergency',
                ]
            );
        });
    }

    private function seedTimeSlots(): Collection
    {
        return TimeSlot::orderBy('id')->get();
    }

    private function seedContent(): void
    {
        $services = [
            ['Emergency Care', '24/7 emergency response with ICU backup.', 'bi-activity'],
            ['Cardiology', 'ECG, echo and angiogram facilities.', 'bi-heart-pulse'],
            ['Diagnostics Lab', '200+ tests with same-day reports.', 'bi-clipboard2-pulse'],
            ['Blood Bank', 'Safe screened blood, all groups.', 'bi-droplet'],
            ['Pharmacy', 'Genuine medicine round the clock.', 'bi-capsule'],
            ['Ambulance', 'City-wide rapid pickup service.', 'bi-truck'],
        ];
        foreach ($services as $i => [$title, $description, $icon]) {
            Service::firstOrCreate(['title' => $title], [
                'description' => $description, 'icon' => $icon, 'order' => $i, 'status' => 1,
            ]);
        }

        foreach (['Your Health, Our Mission', 'Advanced Care Close to Home', 'Trusted Doctors, Modern Labs'] as $i => $title) {
            Slider::firstOrCreate(['title' => $title], [
                'description' => 'Quality healthcare for every family.', 'button_text' => 'Book Appointment',
            ]);
        }

        foreach (['Heart Health', 'Nutrition', 'Child Care', 'Wellness'] as $name) {
            Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
        foreach (['health', 'tips', 'doctor', 'hospital', 'care'] as $name) {
            Tag::firstOrCreate(['slug' => $name], ['name' => $name]);
        }

        $posts = [
            ['10 Tips for a Healthy Heart', 'Heart Health', 'health,tips'],
            ['Child Vaccination Schedule', 'Child Care', 'care,tips'],
            ['When to See a Cardiologist', 'Heart Health', 'doctor,health'],
            ['Eating Right on a Budget', 'Nutrition', 'tips,care'],
            ['Monsoon Health Precautions', 'Wellness', 'hospital,care'],
            ['Understanding Blood Pressure', 'Wellness', 'health,doctor'],
        ];
        foreach ($posts as $i => [$title, $category, $tags]) {
            $blog = Blog::firstOrCreate(['slug' => Str::slug($title)], [
                'title' => $title, 'excerpt' => Str::limit('Practical guidance from our specialists on '.$title.'.', 120),
                'content' => '<p>Our specialists share practical, everyday guidance on '.$title.'. Visit MediCare for a full checkup.</p>',
                'author' => 'MediCare Team', 'status' => 1, 'order' => $i,
                'category' => $category, 'tags' => $tags,
            ]);

            BlogComment::firstOrCreate(
                ['blog_id' => $blog->id, 'email' => 'reader'.$i.'@example.com'],
                ['name' => 'Reader '.($i + 1), 'comment' => 'Very helpful article, thank you!', 'status' => 1]
            );
        }

        $pendingBlog = Blog::first();
        if ($pendingBlog) {
            BlogComment::firstOrCreate(
                ['blog_id' => $pendingBlog->id, 'email' => 'waiting@example.com'],
                ['name' => 'Waiting Visitor', 'comment' => 'Please approve my comment.', 'status' => 0]
            );
        }
    }

    private function seedAppointments(array $users, $doctors, $slots): Collection
    {
        $statuses = [0, 1, 1, 2, 2, 2, 1, 2, 0, 1, 2, 3, 2, 1, 2];
        $created = collect();

        for ($i = 0; $i < 30; $i++) {
            $patient = $users['patients'][$i % count($users['patients'])];
            $doctor = $doctors[$i % $doctors->count()];
            $slot = $slots[$i % $slots->count()];
            // Spread across past weeks, today and the coming days.
            $offset = ($i % 40) - 25;
            $date = Carbon::today()->addDays($offset)->toDateString();
            // Status follows the date: upcoming = awaiting, past = mostly done.
            $status = $offset > 0
                ? [0, 1][$i % 2]
                : ($offset === 0 ? [0, 1, 1][$i % 3] : $statuses[$i % count($statuses)]);

            $created->push(Appointment::firstOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'appointment_date' => $date,
                    'time_slot_id' => $slot->id,
                    'patient_name' => $patient->name,
                ],
                [
                    'user_id' => $patient->id,
                    'age' => 20 + ($i % 45),
                    'gender' => $i % 2 === 0 ? 1 : 2,
                    'phone' => $patient->phone ?? '01700000000',
                    'email' => $patient->email,
                    'visit_type' => ($i % 3) + 1,
                    'status' => $status,
                    'cancellation_token' => Str::random(32),
                ]
            ));
        }

        return $created;
    }

    private function seedLaboratory(array $users, $doctors): void
    {
        $tests = [
            ['CBC (Complete Blood Count)', 'Hematology', 450.00, '4.5-11.0', 'x10^9/L'],
            ['Blood Sugar (Fasting)', 'Biochemistry', 200.00, '70-100', 'mg/dL'],
            ['Lipid Profile', 'Biochemistry', 900.00, '<200', 'mg/dL'],
            ['Liver Function Test', 'Biochemistry', 800.00, '7-56', 'U/L'],
            ['Thyroid (TSH)', 'Hormone', 700.00, '0.4-4.0', 'mIU/L'],
            ['Urine R/E', 'Pathology', 250.00, 'Normal', '-'],
            ['ECG', 'Cardiology', 500.00, 'Normal sinus', '-'],
            ['X-Ray Chest', 'Radiology', 600.00, 'Normal', '-'],
        ];
        $testModels = collect($tests)->map(fn ($t) => LabTest::firstOrCreate(
            ['name' => $t[0]],
            ['category' => $t[1], 'price' => $t[2], 'normal_range' => $t[3], 'unit' => $t[4], 'status' => 1]
        ));

        $statuses = ['completed', 'completed', 'in-progress', 'completed', 'pending', 'completed', 'in-progress', 'completed', 'pending', 'completed', 'completed', 'pending'];
        foreach ($statuses as $i => $status) {
            $patient = $users['patients'][$i % count($users['patients'])];
            $picked = $testModels->shuffle()->take(2);
            $total = (float) $picked->sum('price');

            // Plain creates: one seeder run never duplicates (re-runs skip via marker).
            $order = LabOrder::create(
                [
                    'patient_name' => $patient->name,
                    'phone' => $patient->phone ?? '01700000000',
                    'doctor_id' => $doctors[$i % $doctors->count()]->id,
                    'user_id' => $patient->id,
                    'priority' => $i % 4 === 0 ? 'urgent' : 'normal',
                    'status' => $status,
                    'total' => $total,
                ]
            );

            foreach ($picked as $test) {
                LabOrderItem::create(
                    ['lab_order_id' => $order->id, 'lab_test_id' => $test->id, 'price' => $test->price, 'result' => $status === 'completed' ? 'Within normal limits' : null]
                );
            }

            if ($status === 'completed' && $i % 3 !== 2) {
                $paid = $i % 2 === 0;
                $invoice = Invoice::create(
                    [
                        'invoice_no' => 'INV-2026-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                        'lab_order_id' => $order->id, 'user_id' => $patient->id,
                        'patient_name' => $patient->name, 'phone' => $patient->phone,
                        'subtotal' => $total, 'tax' => 0, 'discount' => 0, 'total' => $total,
                        'status' => $paid ? 'paid' : 'pending',
                        'paid_at' => $paid ? now() : null,
                    ]
                );

                // Snapshot the lab tests as invoice line items so the
                // invoice detail/PDF tables are never empty.
                foreach ($picked as $test) {
                    $invoice->items()->create([
                        'description' => $test->name,
                        'quantity' => 1,
                        'unit_price' => $test->price,
                        'line_total' => $test->price,
                    ]);
                }
            }
        }
    }

    private function seedBlood(array $users, $doctors): void
    {
        $groups = BloodGroup::orderBy('id')->get();
        if ($groups->isEmpty()) {
            return;
        }
        $byName = $groups->keyBy('name');

        $donorNames = ['Abdul Karim', 'Shirin Akter', 'Mohammad Ali', 'Rina Das', 'Faruk Ahmed', 'Lima Begum', 'Sajid Hasan', 'Mina Chowdhury', 'Arif Khan', 'Dipa Rani'];
        $groupNames = ['O+', 'A+', 'B+', 'O-', 'AB+', 'A-', 'B-', 'O+', 'A+', 'AB-'];
        $donors = collect();
        foreach ($donorNames as $i => $name) {
            $group = $byName->get($groupNames[$i % count($groupNames)]) ?? $groups->first();
            $donors->push(BloodDonor::firstOrCreate(
                ['phone' => '018'.str_pad((string) (1000000 + $i), 7, '0', STR_PAD_LEFT)],
                [
                    'name' => $name, 'blood_group_id' => $group->id,
                    'email' => 'donor'.($i + 1).'@example.com',
                    'gender' => $i % 2 === 0 ? 'male' : 'female',
                    'address' => 'Dhaka', 'status' => 1,
                    'last_donation_date' => now()->subDays(100 + $i * 10)->toDateString(),
                ]
            ));
        }

        foreach ($donors as $i => $donor) {
            $expired = $i >= count($donors) - 2;
            BloodDonation::firstOrCreate(
                ['bag_number' => 'BAG-2026-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'donor_id' => $donor->id, 'blood_group_id' => $donor->blood_group_id,
                    'donation_date' => now()->subDays(5 + $i)->toDateString(),
                    'quantity' => 450, 'unit' => 'ml',
                    'expiry_date' => $expired ? now()->subDay()->toDateString() : now()->addDays(30 - $i)->toDateString(),
                    'status' => $expired ? BloodDonation::STATUS_EXPIRED : ($i % 5 === 4 ? BloodDonation::STATUS_TESTING : BloodDonation::STATUS_AVAILABLE),
                ]
            );
        }

        $requestRows = [
            ['pending', 'urgent', 0], ['pending', 'normal', 1], ['pending', 'emergency', 2],
            ['rejected', 'normal', 3], ['cancelled', 'normal', 4],
        ];
        foreach ($requestRows as $i => [$status, $urgency, $pi]) {
            $patient = $users['patients'][$pi];
            $group = $groups[$i % $groups->count()];
            BloodRequest::firstOrCreate(
                ['patient_id' => $patient->id, 'blood_group_id' => $group->id, 'status' => $status],
                [
                    'quantity' => 450, 'unit' => 'ml',
                    'required_date' => now()->addDays($i + 1)->toDateString(),
                    'urgency' => $urgency, 'department' => 'Emergency',
                    'reason' => 'Surgery requirement',
                    'doctor_id' => $doctors[$i % $doctors->count()]->id,
                    'requested_by' => $users['doctorUsers'][$i % count($users['doctorUsers'])]->id,
                ]
            );
        }
    }

    private function seedPrescriptions(array $users, $doctors, $appointments): void
    {
        $completed = $appointments->where('status', 2)->take(6);
        $medicines = [
            ['Napa 500mg', '1 tablet', 'Twice daily', '5 days', 10],
            ['Omeprazole 20mg', '1 capsule', 'Once daily', '7 days', 7],
            ['Azithromycin 500mg', '1 tablet', 'Once daily', '3 days', 3],
            ['Vitamin D3', '1 softgel', 'Once weekly', '4 weeks', 4],
        ];

        foreach ($completed as $i => $appointment) {
            $patientUser = User::where('email', $appointment->email)->first();
            $prescription = Prescription::firstOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'doctor_id' => $appointment->doctor_id,
                    'patient_user_id' => $patientUser?->id,
                    'patient_name' => $appointment->patient_name,
                    'age' => $appointment->age, 'gender' => $appointment->gender,
                    'phone' => $appointment->phone,
                    'symptoms' => 'Fever and weakness for 3 days.',
                    'diagnosis' => 'Viral fever.',
                    'advice' => 'Rest, fluids, follow-up after 5 days.',
                ]
            );

            $med = $medicines[$i % count($medicines)];
            PrescriptionItem::firstOrCreate(
                ['prescription_id' => $prescription->id, 'medicine_name' => $med[0]],
                ['dosage' => $med[1], 'frequency' => $med[2], 'duration' => $med[3], 'quantity' => $med[4]]
            );
        }
    }

    private function seedSettings(): void
    {
        if (GeneralSetting::count() === 0) {
            GeneralSetting::create([
                'site_name' => 'MediCare Hospital',
                'address' => '12 Green Road, Dhaka 1215',
                'working_hours' => 'Open 24 Hours',
                'facebook' => 'https://facebook.com/medicare',
                'twitter' => 'https://x.com/medicare',
                'linkedin' => 'https://linkedin.com/company/medicare',
                'youtube' => 'https://youtube.com/@medicare',
            ]);
        }
    }
}

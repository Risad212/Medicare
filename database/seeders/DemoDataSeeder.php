<?php

namespace Database\Seeders;

use App\Models\AboutSetting;
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
use App\Models\HomeSetting;
use App\Models\Invoice;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabTest;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\ServiceSetting;
use App\Models\Slider;
use App\Models\Tag;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
            $this->seedFrontendMedia();
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

    private function seedFrontendMedia(): void
    {
        $map = [
            'sliders/slider-1.jpg' => 'media/home/slider-1.jpg',
            'sliders/slider-2.jpg' => 'media/home/slider-2.jpg',
            'sliders/slider-3.png' => 'media/home/slider-3.png',
            'home/about-1.jpg' => 'media/home/about-1.jpg',
            'home/about-2.jpg' => 'media/home/about-2.jpg',
            'home/about-3.jpeg' => 'media/home/about-3.jpeg',
            'doctors/doctor-1.png' => 'media/home/doctor-1.png',
            'doctors/doctor-2.png' => 'media/home/doctor-2.png',
            'doctors/doctor-3.png' => 'media/home/doctor-3.png',
            'blogs/blog-1.jpeg' => 'media/home/blog.jpeg',
            'blogs/blog-2.jpg' => 'media/home/blog2.jpg',
            'blogs/blog-3.jpg' => 'media/home/blog3.jpg',
            'blogs/blog-4.jpg' => 'media/home/blog4.jpg',
            'about/about-1.jpg' => 'media/about/about-1.jpg',
            'about/about-2.jpg' => 'media/about/about-2.jpg',
            'service/emergency.jpg' => 'media/service/emargency.jpg',
            'settings/logo.png' => 'media/common/logo.png',
            'settings/footer-logo.png' => 'media/common/logo.png',
            'services/icons/emergency.svg' => 'media/service/icons/emergency.svg',
            'services/icons/cardiology.svg' => 'media/service/icons/cardiology.svg',
            'services/icons/lab.svg' => 'media/service/icons/lab.svg',
            'services/icons/blood.svg' => 'media/service/icons/blood.svg',
            'services/icons/pharmacy.svg' => 'media/service/icons/pharmacy.svg',
            'services/icons/ambulance.svg' => 'media/service/icons/ambulance.svg',
        ];

        foreach ($map as $target => $source) {
            $sourcePath = public_path('frontend-assets/'.$source);
            $targetPath = storage_path('app/public/'.$target);

            if (! File::exists($targetPath) && File::exists($sourcePath)) {
                File::ensureDirectoryExists(dirname($targetPath));
                File::copy($sourcePath, $targetPath);
            }
        }
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

        $images = ['doctors/doctor-1.png', 'doctors/doctor-2.png', 'doctors/doctor-3.png'];

        return collect($rows)->map(function ($row, $i) use ($users, $images) {
            [$name, $department, $specialist, $degree, $availability] = $row;

            return Doctor::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name, 'degree' => $degree, 'department' => $department,
                    'specialist' => $specialist, 'availability' => $availability,
                    'phone' => '01'.rand(700000000, 799999999), 'status' => 1,
                    'user_id' => $users['doctorUsers'][$i]->id,
                    'services' => 'Consultation, Follow-up, Emergency',
                    'image' => $images[$i % count($images)],
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
            ['Emergency Care', 'Round-the-clock emergency unit with ICU backup and rapid triage, so critical patients are stabilised within minutes of arrival.', 'services/icons/emergency.svg'],
            ['Cardiology', 'Complete heart care — ECG, echocardiogram, stress testing and angiogram support under senior cardiologists.', 'services/icons/cardiology.svg'],
            ['Diagnostics Lab', 'More than 200 laboratory and imaging tests with accurate, same-day reports you can collect online.', 'services/icons/lab.svg'],
            ['Blood Bank', 'Safe, screened blood of every group with a verified donor network for urgent transfusion needs.', 'services/icons/blood.svg'],
            ['Pharmacy', 'Genuine medicine at fair prices, dispensed by qualified pharmacists day and night.', 'services/icons/pharmacy.svg'],
            ['Ambulance', 'City-wide rapid ambulance pickup with trained responders and direct hospital handover.', 'services/icons/ambulance.svg'],
        ];
        foreach ($services as $i => [$title, $description, $icon]) {
            Service::updateOrCreate(['title' => $title], [
                'description' => $description, 'icon' => $icon, 'order' => $i, 'status' => 1,
                'button_text' => 'Read more', 'button_url' => '#',
            ]);
        }

        $sliders = [
            ['Advanced Healthcare You Can Trust', 'From emergency response to specialised surgery, MediCare Hospital brings modern diagnostics, expert doctors and 24/7 care under one roof.', 'Book Appointment', 'sliders/slider-1.jpg'],
            ['Your Health, Our Mission', 'Book appointments online in a minute, meet specialists across six departments and get same-day lab reports.', 'Book Appointment', 'sliders/slider-2.jpg'],
            ['Emergency Care, Around the Clock', 'Our emergency unit, ICU backup and rapid ambulance service respond within minutes — day or night.', 'Call Emergency', 'sliders/slider-3.png'],
        ];
        foreach ($sliders as [$title, $description, $button, $image]) {
            Slider::updateOrCreate(['title' => $title], [
                'description' => $description, 'button_text' => $button, 'bg_image' => $image,
            ]);
        }

        foreach (['Heart Health', 'Nutrition', 'Child Care', 'Wellness'] as $name) {
            Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
        foreach (['health', 'tips', 'doctor', 'hospital', 'care'] as $name) {
            Tag::firstOrCreate(['slug' => $name], ['name' => $name]);
        }

        $posts = [
            ['10 Tips for a Healthy Heart', 'Heart Health', 'health,tips', 'blogs/blog-1.jpeg', 'Small daily habits — walking, less salt, regular checkups — keep your heart strong. Our cardiologists explain what works.',
                '<p><strong>1. Walk briskly for 30 minutes a day.</strong> You do not need a gym. A daily walk around your neighbourhood strengthens the heart muscle, lowers blood pressure and burns extra calories.</p><p><strong>2. Cut salt gradually.</strong> Most extra salt hides in packaged food, pickles and restaurant meals. Cook fresh at home and flavour with lemon, garlic and spices instead.</p><p><strong>3. Eat more fibre.</strong> Lentils, chickpeas, oats, vegetables and seasonal fruit keep cholesterol down and keep you full for longer.</p><p><strong>4. Quit smoking completely.</strong> Even a few cigarettes a day damage blood vessels. Ask our physicians about quit-support plans that actually work.</p><p><strong>5. Know your numbers.</strong> Check blood pressure, blood sugar and cholesterol at least once a year after age 35 — silent problems are the most dangerous.</p><p><strong>6. Sleep seven to eight hours.</strong> Poor sleep raises blood pressure and stress hormones. Keep a regular bedtime, even on weekends.</p><p><strong>7. Manage stress.</strong> Deep breathing, prayer, time with family or a short evening walk all lower the strain on your heart.</p><p><strong>8. Keep a healthy weight.</strong> Losing even 5 percent of body weight meaningfully reduces heart risk. Small, steady changes beat crash diets.</p><p><strong>9. Limit sugary drinks.</strong> One bottle of soft drink can hold ten spoons of sugar. Choose water, lemon water or unsweetened tea.</p><p><strong>10. See a cardiologist early.</strong> Chest discomfort, unusual breathlessness or a strong family history of heart disease deserve a checkup now — not later. Our cardiology unit offers ECG, echocardiogram and stress testing under one roof.</p>'],
            ['Child Vaccination Schedule', 'Child Care', 'care,tips', 'blogs/blog-2.jpg', 'Which vaccines your child needs and when. A simple schedule every parent can follow from birth to age five.',
                '<p>Vaccines protect your child against polio, measles, tuberculosis, hepatitis and many more serious diseases. Follow this routine schedule and keep the vaccination card safe — schools and travel often ask for it.</p><ul><li><strong>At birth:</strong> BCG, OPV-0 and Hepatitis B birth dose.</li><li><strong>6 weeks:</strong> Pentavalent-1, OPV-1, PCV-1 and Rotavirus-1.</li><li><strong>10 weeks:</strong> Pentavalent-2, OPV-2, PCV-2 and Rotavirus-2.</li><li><strong>14 weeks:</strong> Pentavalent-3, OPV-3, PCV-3 and IPV.</li><li><strong>9 months:</strong> Measles-Rubella first dose and Vitamin A.</li><li><strong>15 months:</strong> Measles-Rubella second dose.</li></ul><p>Mild fever or a sore leg after vaccination is normal and fades within a day or two. Give paracetamol only if our pediatrician advises, and return immediately for high fever, continuous crying or swelling that spreads.</p><p>Missed a dose? Do not restart the whole course — visit our pediatric OPD and we will fit the missing vaccines into a catch-up plan based on age.</p>'],
            ['When to See a Cardiologist', 'Heart Health', 'doctor,health', 'blogs/blog-3.jpg', 'Chest discomfort, breathlessness or high blood pressure? Know the warning signs that deserve a specialist visit.',
                '<p>Heart disease rarely arrives without warning. Your body usually signals for weeks or months first — learning these signs can save a life.</p><ul><li><strong>Chest pressure or heaviness</strong> that comes with exertion and eases with rest.</li><li><strong>Breathlessness</strong> during ordinary activity, or needing extra pillows to sleep comfortably.</li><li><strong>Palpitations</strong> — a racing, fluttering or skipping heartbeat.</li><li><strong>Unexplained fatigue</strong>, dizziness or fainting spells.</li><li><strong>Swollen ankles</strong> or sudden weight gain from fluid retention.</li><li><strong>Readings that stay high:</strong> blood pressure above 140/90 or fasting sugar above 126 on repeat checks.</li></ul><p>Call emergency immediately for crushing chest pain lasting more than five minutes, pain spreading to the arm, neck or jaw, or sudden cold sweating with nausea.</p><p>At your first cardiology visit, expect an ECG, an echocardiogram if needed, blood tests for cholesterol and sugar, and a clear treatment plan — many patients are managed with medicine and lifestyle alone, without any procedure.</p>'],
            ['Eating Right on a Budget', 'Nutrition', 'tips,care', 'blogs/blog-4.jpg', 'Healthy eating does not need to be expensive. Local foods and smart planning cover everything your body needs.',
                '<p>Nutritious food is already in your local bazaar — it just takes a little planning to put it on the plate every day.</p><ul><li><strong>Build meals on lentils and rice.</strong> Dal, chickpeas and beans give protein at a fraction of meat prices.</li><li><strong>Buy seasonal produce.</strong> In-season papaya, guava, leafy greens and gourds cost less and carry more nutrients.</li><li><strong>Choose small fish and eggs.</strong> They match big fish for protein and omega nutrition at far lower cost.</li><li><strong>Cook once, eat twice.</strong> A large pot of dal or curry stretches across lunch and dinner with no extra fuel.</li><li><strong>Drink water first.</strong> Safe boiled or filtered water beats every bottled drink for health and savings.</li></ul><p>Cut back on packet snacks, sweet buns and deep-fried street food — cheap per bite, costly for blood pressure, sugar and weight. If diabetes or hypertension runs in your family, our nutrition counsellors will shape a monthly meal plan around your actual bazaar budget.</p>'],
            ['Monsoon Health Precautions', 'Wellness', 'hospital,care', 'blogs/blog-1.jpeg', 'Waterborne illness rises every monsoon. Safe water, food hygiene and timely vaccination keep your family protected.',
                '<p>Every monsoon brings the same visitors: diarrhoea, typhoid, dengue, flu and skin infections. A few habits keep your household safe through the season.</p><ul><li><strong>Drink only safe water.</strong> Boil for a full minute or use a proper filter, and scrub storage containers weekly.</li><li><strong>Eat freshly cooked food.</strong> Avoid cut fruit, roadside juices and stale items; reheat leftovers until steaming.</li><li><strong>Stop mosquitoes breeding.</strong> Empty coolers, tyres, coconut shells and trays twice a week, and sleep under nets where needed.</li><li><strong>Keep feet dry.</strong> Wash and dry between the toes daily to prevent fungal infections during waterlogging.</li><li><strong>Vaccinate on time.</strong> Typhoid and hepatitis A vaccines are worth discussing before the rains peak.</li></ul><p>See a doctor promptly for fever lasting more than two days, bloody diarrhoea, signs of dehydration or breathlessness. Our emergency unit and lab stay open round the clock through the season, with dengue NS1 and typhoid testing reported the same day.</p>'],
            ['Understanding Blood Pressure', 'Wellness', 'health,doctor', 'blogs/blog-2.jpg', 'What your readings mean, how to measure correctly at home, and when medication becomes necessary.',
                '<p>Blood pressure is written as two numbers — for example 120/80. The upper (systolic) measures pressure as the heart beats; the lower (diastolic) measures it at rest. Here is what the bands mean for adults:</p><ul><li><strong>Below 120/80:</strong> normal — keep up your habits.</li><li><strong>120–139 / 80–89:</strong> elevated — lifestyle changes now can avoid medicine later.</li><li><strong>140/90 or above, repeatedly:</strong> hypertension — see a physician for a treatment plan.</li><li><strong>Above 180/120 with symptoms:</strong> seek emergency care immediately.</li></ul><p>Measure correctly: rest five minutes, sit with back supported and feet flat, keep the cuff at heart level, and avoid tea, coffee or smoking for 30 minutes before. Take two readings and note the average.</p><p>Most hypertension is controlled with steady habits — less salt, daily walking, healthy weight, limited alcohol — plus one small daily tablet where prescribed. Never stop medicine because one reading looks good; review with our physicians every three to six months instead.</p>'],
        ];
        foreach ($posts as $i => [$title, $category, $tags, $image, $excerpt, $body]) {
            $blog = Blog::updateOrCreate(['slug' => Str::slug($title)], [
                'title' => $title, 'excerpt' => $excerpt, 'image' => $image,
                'content' => '<p>'.$excerpt.'</p>'.$body.'<p>At MediCare Hospital, our specialists combine modern diagnostics with personal guidance. Book a consultation to get advice shaped around your health, not generic tips — and return for regular follow-ups so small issues never become big ones.</p>',
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
        $general = GeneralSetting::first();
        $generalData = [
            'site_name' => 'MediCare Hospital',
            'logo' => 'settings/logo.png',
            'footer_logo' => 'settings/footer-logo.png',
            'address' => '12 Green Road, Dhaka 1215',
            'working_hours' => 'Open 24 Hours, Every Day',
            'phone' => '+880 2-5815-1234',
            'email' => 'info@medicarehospital.com',
            'footer_description' => 'MediCare Hospital has served Dhaka families for over 25 years with specialist doctors, a modern diagnostics lab, a safe blood bank and round-the-clock emergency care.',
            'copyright' => '© '.now()->year.' MediCare Hospital. All rights reserved.',
            'facebook' => 'https://facebook.com/medicare',
            'twitter' => 'https://x.com/medicare',
            'linkedin' => 'https://linkedin.com/company/medicare',
            'youtube' => 'https://youtube.com/@medicare',
        ];
        if (! $general) {
            GeneralSetting::create($generalData);
        } else {
            foreach ($generalData as $key => $value) {
                if (empty($general->{$key})) {
                    $general->{$key} = $value;
                }
            }
            $general->save();
        }

        HomeSetting::firstOrCreate([], [
            'about_title' => 'A Modern Hospital Built Around Patients',
            'about_description' => 'For over 25 years, MediCare Hospital has combined experienced specialists with modern diagnostics — from cardiology and neurology to pediatrics and emergency care. Book online in a minute, meet your doctor without long queues, and collect lab reports the same day.',
            'about_button_text' => 'More About Us',
            'about_image_one' => 'home/about-1.jpg',
            'about_image_two' => 'home/about-2.jpg',
            'about_image_three' => 'home/about-3.jpeg',
            'counter_one_number' => 25, 'counter_one_text' => 'Years of Experience',
            'counter_two_number' => 150, 'counter_two_text' => 'Specialist Doctors',
            'counter_three_number' => 48000, 'counter_three_text' => 'Happy Patients',
            'counter_four_number' => 15, 'counter_four_text' => 'Medical Departments',
        ]);

        AboutSetting::firstOrCreate([], [
            'subtitle' => 'About MediCare',
            'title' => 'Compassionate Care Backed by Modern Medicine',
            'tagline' => 'Trusted by Dhaka families since 2000',
            'description' => 'MediCare Hospital began as a small clinic with one promise: no patient should wait for quality care. Today our 150+ specialists, 200-test diagnostics lab, safe blood bank and 24/7 emergency unit serve thousands of families every month — with online booking, transparent pricing and follow-up that continues after discharge.',
            'button_text' => 'Meet Our Doctors',
            'button_url' => '/doctor',
            'image_one' => 'about/about-1.jpg',
            'image_two' => 'about/about-2.jpg',
            'mission_title' => 'Our Mission',
            'mission_description' => 'To make advanced healthcare reachable for every family — accurate diagnosis, honest advice and treatment without delay.',
            'planning_title' => 'Our Approach',
            'planning_description' => 'Specialist consultation, same-day diagnostics and coordinated follow-up, all managed through one patient record.',
            'vision_title' => 'Our Vision',
            'vision_description' => 'A healthier Bangladesh where world-class hospital care is available in every neighbourhood, not just abroad.',
        ]);

        $serviceSetting = ServiceSetting::firstOrCreate([], [
            'emergency_subtitle' => 'Emergency Treatment',
            'emergency_title' => 'Emergency? Call Us Any Time, Day or Night',
            'emergency_description' => 'Chest pain, accidents, complications in pregnancy — our emergency team triages within minutes, with ICU backup, an on-call surgeon and a stocked blood bank on site. One call dispatches our ambulance and prepares your bed before you arrive.',
            'emergency_image' => 'service/emergency.jpg',
            'emergency_phone' => '+880 2-5815-1234',
            'emergency_email' => 'emergency@medicarehospital.com',
            'prevention_subtitle' => 'Prevention',
            'prevention_title' => 'How To Protect Yourself',
            'prevention_1_title' => 'Wash Your Hands',
            'prevention_1_desc' => 'Scrub with soap for at least 20 seconds before meals and after returning home — the simplest shield against infection.',
            'prevention_2_title' => 'Stay At Home When Sick',
            'prevention_2_desc' => 'Rest, hydrate and avoid crowds during fever or flu so you recover faster and protect those around you.',
            'prevention_3_title' => 'Avoid Close Contact',
            'prevention_3_desc' => 'Keep distance from anyone coughing or sneezing, and wear a mask in crowded indoor places.',
            'prevention_4_title' => 'Eat Balanced Meals',
            'prevention_4_desc' => 'Fresh vegetables, lentils, fish and clean water every day keep immunity strong without costly supplements.',
            'prevention_5_title' => 'Exercise Regularly',
            'prevention_5_desc' => 'Thirty minutes of brisk walking most days lowers blood pressure, sugar and stress alike.',
            'prevention_6_title' => 'Get Regular Checkups',
            'prevention_6_desc' => 'Yearly screening catches diabetes, hypertension and heart risk early — when treatment is simplest.',
            'prevention_7_title' => 'Washing Hands',
            'prevention_7_desc' => 'Carry soap or sanitizer when travelling so clean hands are always within reach, wherever you are.',
            'prevention_8_title' => 'Use Your Gloves',
            'prevention_8_desc' => 'Wear gloves when caring for a sick family member or handling waste, and dispose of them safely afterwards.',
        ]);
        $serviceSetting->fill([
            'prevention_7_title' => 'Washing Hands',
            'prevention_7_desc' => 'Carry soap or sanitizer when travelling so clean hands are always within reach, wherever you are.',
            'prevention_8_title' => 'Use Your Gloves',
            'prevention_8_desc' => 'Wear gloves when caring for a sick family member or handling waste, and dispose of them safely afterwards.',
        ]);
        $serviceSetting->save();

        $seoRows = [
            'home' => ['MediCare Hospital — Advanced Healthcare in Dhaka', 'Specialist doctors, modern diagnostics lab, blood bank and 24/7 emergency care at MediCare Hospital, Dhaka. Book appointments online.', 'hospital, doctors, appointment, diagnostics, emergency, Dhaka'],
            'about' => ['About Us — MediCare Hospital', 'Trusted by Dhaka families since 2000. Meet our mission, our specialists and our modern facilities.', 'about hospital, mission, vision, Dhaka hospital'],
            'service' => ['Our Services — MediCare Hospital', 'Emergency care, cardiology, diagnostics lab, blood bank, pharmacy and ambulance services under one roof.', 'hospital services, emergency, cardiology, lab, pharmacy'],
            'doctor' => ['Our Doctors — MediCare Hospital', 'Meet experienced specialists across cardiology, neurology, orthopedics, pediatrics and more. Book your visit online.', 'doctors, specialists, appointment'],
            'blog' => ['Health Blog — MediCare Hospital', 'Practical health guidance from our specialists on heart, nutrition, child care and everyday wellness.', 'health blog, tips, wellness'],
            'contact' => ['Contact Us — MediCare Hospital', 'Reach MediCare Hospital at 12 Green Road, Dhaka. Call, email or send a message — we reply within one working day.', 'contact hospital, address, phone'],
            'appointment' => ['Book Appointment — MediCare Hospital', 'Choose your doctor, date and time slot online. Instant confirmation with live status tracking.', 'book appointment, doctor visit'],
        ];
        foreach ($seoRows as $page => [$title, $description, $keywords]) {
            SeoSetting::firstOrCreate(['page' => $page], [
                'meta_title' => $title, 'meta_description' => $description, 'meta_keywords' => $keywords,
            ]);
        }
    }
}

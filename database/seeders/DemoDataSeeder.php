<?php

namespace Database\Seeders;

use App\Models\AboutSetting;
use App\Models\Appointment;
use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\Category;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorOffDay;
use App\Models\DoctorReview;
use App\Models\DoctorSchedule;
use App\Models\Faq;
use App\Models\GalleryImage;
use App\Models\GeneralSetting;
use App\Models\HomeSetting;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\ServiceSetting;
use App\Models\Slider;
use App\Models\Tag;
use App\Models\TimeSlot;
use App\Models\User;
use App\Modules\Ambulance\Models\AmbulanceRequest;
use App\Modules\Beds\Models\Bed;
use App\Modules\Beds\Models\Room;
use App\Modules\Beds\Models\Ward;
use App\Modules\BloodBank\Models\BloodDonation;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Models\BloodGroup;
use App\Modules\BloodBank\Models\BloodIssue;
use App\Modules\BloodBank\Models\BloodRequest;
use App\Modules\Lab\Models\LabOrder;
use App\Modules\Lab\Models\LabOrderItem;
use App\Modules\Lab\Models\LabReport;
use App\Modules\Lab\Models\LabTest;
use App\Modules\Pharmacy\Models\Medicine;
use App\Modules\Vaccination\Models\Vaccination;
use App\Modules\Zoom\Models\ZoomMeeting;
use App\Services\PatientNotifier;
use App\Services\StaffNotifier;
use App\Support\Module;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public static function isAllowed(): bool
    {
        if (app()->environment('production')) {
            return false;
        }

        return app()->environment(['local', 'testing']) || (bool) config('app.demo_data_enabled', false);
    }

    /**
     * Fill the app with realistic demo content so every module looks alive.
     * Every section is gap-filling and idempotent: running the seeder again
     * tops up whatever is missing without duplicating what already exists.
     */
    public function run(): void
    {
        if (! self::isAllowed()) {
            Log::notice('Demo data seeding was skipped for this environment.');

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
            $this->relinkGuestRecords();
            $this->seedPatients($users);
            $this->seedDoctorSchedules($doctors, $slots);
            $this->seedDoctorOffDays($doctors);
            if (Module::enabled('lab')) {
                $this->seedLaboratory($users, $doctors);
                $this->seedLabReports($users);
            }
            if (Module::enabled('bloodbank')) {
                $this->seedBlood($users, $doctors);
                $this->seedBloodIssues($users, $doctors);
            }
            if (Module::enabled('pharmacy')) {
                $this->seedMedicines();
            }
            if (Module::enabled('vaccination')) {
                $this->seedVaccinations($users);
            }
            if (Module::enabled('beds')) {
                $this->seedBeds($users);
            }
            if (Module::enabled('ambulance')) {
                $this->seedAmbulanceRequests($users);
            }
            if (Module::enabled('zoom')) {
                $this->seedZoomMeetings();
            }
            $this->seedPrescriptions($users, $doctors, $appointments);
            if (Module::enabled('reviews')) {
                $this->seedReviews();
            }
            $this->seedFaqs();
            $this->seedGallery();
            $this->seedNotifications();
            $this->seedSettings();
        });

        $this->command->info('Demo data ready. Admin login: admin@medicare.test / 123456');
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
        $password = Hash::make('123456');

        $admin = User::firstOrCreate(
            ['email' => 'admin@medicare.test'],
            [
                'name' => 'Hospital Admin',
                'password' => $password, 'role' => 'admin', 'email_verified_at' => now(),
            ]
        );

        $staffMap = [
            ['Front Desk Officer', 'receptionist@medicare.test', 'receptionist', 'receptionist'],
            ['Lab Technologist', 'lab@medicare.test', 'lab-technician', 'lab-technician'],
            ['Duty Pharmacist', 'pharmacist@medicare.test', 'pharmacist', 'pharmacist'],
        ];
        $staff = [];
        foreach ($staffMap as [$name, $email, $role, $slug]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name, 'password' => $password,
                    'role' => $role, 'phone' => '01'.rand(300000000, 399999999), 'email_verified_at' => now(),
                ]
            );
            $staff[] = $user;
        }

        $patientNames = ['Rahim Uddin', 'Fatema Begum', 'Karim Sheikh', 'Ayesha Siddika', 'Mohan Das', 'Nusrat Jahan', 'Habib Rahman', 'Salma Akter'];
        $patients = [];
        foreach ($patientNames as $i => $name) {
            $patients[] = User::firstOrCreate(
                ['email' => 'patient'.($i + 1).'@medicare.test'],
                [
                    'name' => $name, 'password' => $password, 'role' => 'patient',
                    'phone' => '01'.rand(300000000, 399999999),
                    'gender' => $i % 2 === 0 ? 'male' : 'female',
                    'blood_group' => ['A+', 'B+', 'O+', 'AB+'][$i % 4],
                    'address' => 'House '.($i + 3).', Dhaka',
                    'email_verified_at' => now(),
                ]
            );
        }

        $doctorUsers = [];
        for ($i = 1; $i <= 6; $i++) {
            $doctorUsers[] = User::firstOrCreate(
                ['email' => 'doctor'.$i.'@medicare.test'],
                [
                    'name' => 'Doctor '.$i, 'password' => $password,
                    'role' => 'doctor', 'email_verified_at' => now(),
                ]
            );
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

            $doctor = Doctor::firstOrCreate(
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

            // Re-attach the login account when the doctor row exists but its
            // user link was lost (e.g. demo users were recreated).
            if (! $doctor->user_id) {
                $doctor->update(['user_id' => $users['doctorUsers'][$i]->id]);
            }

            if (! $doctor->image) {
                $doctor->update(['image' => $images[$i % count($images)]]);
            }

            return $doctor;
        });
    }

    private function seedTimeSlots(): Collection
    {
        return TimeSlot::orderBy('id')->get();
    }

    private function seedContent(): void
    {
        $services = [
            ['Emergency Care', 'emergency-care', 'services/icons/emergency.svg', "Round-the-clock emergency unit with ICU backup and rapid triage, so critical patients are stabilised within minutes of arrival.\nOur emergency floor covers chest pain, strokes, road accidents, breathing difficulty, high fever, pregnancy complications and paediatric emergencies. On-call surgeons, anaesthetists and physicians are present day and night, supported by bedside X-ray, ECG, an emergency laboratory and a stocked pharmacy on the same floor.\nEvery arrival is triaged within minutes: life-threatening cases go straight to resuscitation bays while stable patients are streamed to fast-track consultation rooms, so nobody waits behind the wrong queue. Serious cases move directly to ICU, operation theatre or cardiac care without repeated paperwork.\nOne call to our hotline dispatches the ambulance and prepares your bed before you arrive. Bring any previous prescriptions or reports if available, but never delay coming — treatment starts first, formalities follow."],
            ['Cardiology', 'cardiology', 'services/icons/cardiology.svg', "Complete heart care — ECG, echocardiogram, stress testing and angiogram support under senior cardiologists.\nWe diagnose and treat hypertension, chest pain, arrhythmia, valve disease and heart failure using modern non-invasive testing first. Our cardiac OPD offers same-week appointments, and emergency cardiac cases are accepted round the clock with direct CCU admission.\nPrevention is half the treatment here: executive heart checkup packages combine ECG, echo, lipid profile, blood sugar and physician review in a single morning visit, with a written risk plan for diet, exercise and medicine.\nAfter any cardiac event or procedure, structured follow-ups track your recovery — medication review, physiotherapy guidance and repeat testing at the right intervals. Family members receive clear counselling on warning signs, so the next emergency is caught even earlier."],
            ['Diagnostics Lab', 'diagnostics-lab', 'services/icons/lab.svg', "More than 200 laboratory and imaging tests with accurate, same-day reports you can collect online.\nOur menu covers blood, urine and stool analysis, hormones, diabetes and thyroid panels, lipid profiles, liver and kidney function, ECG, X-ray and ultrasound. Samples are collected from 8 AM daily, and most routine reports are ready the same afternoon.\nQuality is controlled at every step: barcoded samples, calibrated analysers, consultant pathologist verification and critical-value phone alerts to your doctor within the hour. Home sample collection runs across the city for elderly patients, children and busy professionals.\nBring previous reports for comparison — trends across months tell our doctors far more than a single reading. Corporate and pre-employment health packages with discounted bundling are available at the front desk."],
            ['Blood Bank', 'blood-bank', 'services/icons/blood.svg', "Safe, screened blood of every group with a verified donor network for urgent transfusion needs.\nEvery donated unit is screened for infections, typed and cross-matched before issue, and stored under monitored refrigeration with full component separation into red cells, plasma and platelets — so patients receive exactly what their condition requires.\nOur registered donor panel covers all eight blood groups for planned surgeries, thalassemia and dialysis support, while the 24-hour issue counter serves road-accident and maternity emergencies without waiting for morning.\nHealthy adults between 18 and 60 can donate in under 30 minutes at our donor lounge, and regular donors receive free haemoglobin and blood-group checks. One donation can support up to three patients — register once and we will call you when your group is needed."],
            ['Pharmacy', 'pharmacy', 'services/icons/pharmacy.svg', "Genuine medicine at fair prices, dispensed by qualified pharmacists day and night.\nWe stock prescriptions from every department — cardiac, diabetic, paediatric, antibiotic and post-surgical lines — sourced from licensed distributors with strict batch and expiry control. Cold-chain items such as insulin and vaccines are stored in monitored refrigerators.\nEvery prescription is reviewed before dispensing: our pharmacists check dosage, interactions and duplicates, then explain timing, food rules and storage in plain language. Unclear handwriting or unusual doses are confirmed with the prescribing doctor before anything leaves the counter.\nChronic patients can enrol in monthly refill plans so diabetes, pressure and thyroid medicines are packed and ready on schedule, with reminders before the strip runs out. Emergency and post-discharge prescriptions are prioritised at a dedicated counter."],
            ['Ambulance', 'ambulance', 'services/icons/ambulance.svg', "City-wide rapid ambulance pickup with trained responders and direct hospital handover.\nA single hotline coordinates our fleet across all major neighbourhoods. Each ambulance carries oxygen, suction, first-aid, a stretcher and a trained paramedic who begins basic stabilisation — bleeding control, positioning, oxygen — during the journey, not after arrival.\nWhile en route, the crew alerts the emergency floor with your condition and location, so the triage team, bed and relevant specialist are waiting at the gate. Critical cases are received directly into resuscitation without registration delays.\nKeep our hotline saved in every family phone, and when calling, share the nearest landmark plus a callback number. Inter-facility transfers with doctor or nurse escort can be arranged for patients referred from outside clinics."],
        ];
        foreach ($services as $i => [$title, $slug, $icon, $description]) {
            Service::updateOrCreate(['title' => $title], [
                'slug' => $slug, 'description' => $description, 'icon' => $icon, 'order' => $i, 'status' => 1,
                'button_text' => 'Read more', 'button_url' => null,
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
            ['5 Early Signs of Diabetes', 'Wellness', 'health,care', 'blogs/blog-3.jpg', 'Constant thirst, frequent urination and slow-healing cuts? These early warnings deserve a blood sugar check.',
                '<p>Diabetes develops quietly over months or years. Catching these early signs means simpler treatment — sometimes lifestyle alone.</p><ul><li><strong>Excessive thirst and dry mouth</strong> that water never quite fixes.</li><li><strong>Frequent urination</strong>, especially waking up several times at night.</li><li><strong>Constant tiredness</strong> even after a full night of sleep.</li><li><strong>Blurred vision</strong> that comes and goes through the day.</li><li><strong>Cuts and pimples that heal slowly</strong>, or repeated skin infections.</li></ul><p>A simple fasting blood sugar test confirms it in minutes. If your reading is high, our physicians build a practical plan — food, walking, weight and medicine only where needed — with reviews every three months.</p>'],
            ['How to Prepare for a Blood Test', 'Wellness', 'tips,care', 'blogs/blog-4.jpg', 'Fasting rules, medicines and timing — small preparations that keep your lab results accurate.',
                '<p>Accurate reports start before the needle. Follow these steps and your results will truly reflect your health.</p><ul><li><strong>Fast as instructed.</strong> Sugar, lipid and most metabolic panels need 8–12 hours without food; water is fine unless told otherwise.</li><li><strong>Keep taking prescribed medicine</strong> unless the doctor specifically pauses it — stopping on your own skews results.</li><li><strong>Avoid heavy exercise and alcohol</strong> for 24 hours before liver, muscle and hormone tests.</li><li><strong>Come in the morning.</strong> Hormone and iron levels shift through the day, so early samples compare best.</li><li><strong>Bring past reports.</strong> Trends across months tell our pathologists far more than one reading.</li></ul><p>Our lab collects samples from 8 AM with same-day digital reports, and home collection is available across the city for elderly or busy patients.</p>'],
            ['Back Pain: Causes and Relief', 'Wellness', 'health,tips', 'blogs/blog-1.jpeg', 'Most back pain comes from posture and weak core muscles — not slipped discs. Here is what helps.',
                '<p>Nine out of ten backaches are mechanical: long sitting hours, phone-neck, soft mattresses and sudden lifting. Serious causes are rare but must be ruled out.</p><ul><li><strong>Move gently, do not freeze.</strong> Short walks and light stretching heal faster than complete bed rest.</li><li><strong>Fix your workstation.</strong> Screen at eye level, feet flat, and stand or stretch every 45 minutes.</li><li><strong>Strengthen the core.</strong> Ten minutes of daily bridging and plank-style holds support the spine.</li><li><strong>Watch the red flags.</strong> Pain after a fall, numbness in the legs, fever with backache or loss of bladder control needs urgent review.</li></ul><p>Our orthopedics team starts with examination and physiotherapy — X-ray or MRI only where truly indicated — so most patients recover without surgery or heavy medication.</p>'],
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
        // Existing bookings are kept as-is; the guest pass below only runs
        // on an empty calendar (the first seed).
        if (Appointment::count() > 0) {
            return Appointment::all();
        }

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

        // Orders and invoices below are plain creates (they carry unique
        // invoice numbers), so they only run when the module is empty.
        if (LabOrder::count() > 0) {
            return;
        }

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

    /**
     * Re-attach guest records to the demo patient accounts by e-mail.
     * Historic rows survive user deletion with a null link; this tops
     * those links back up so patient profiles show their history.
     */
    private function relinkGuestRecords(): void
    {
        $byEmail = User::where('role', 'patient')->pluck('id', 'email');
        if ($byEmail->isEmpty()) {
            return;
        }

        $emails = $byEmail->keys()->all();

        Appointment::whereNull('user_id')->whereIn('email', $emails)->get()
            ->each(fn (Appointment $appointment) => $appointment->update(['user_id' => $byEmail[$appointment->email]]));

        LabOrder::whereNull('user_id')->whereIn('email', $emails)->get()
            ->each(fn (LabOrder $order) => $order->update(['user_id' => $byEmail[$order->email]]));

        Invoice::whereNull('user_id')->whereIn('email', $emails)->get()
            ->each(fn (Invoice $invoice) => $invoice->update(['user_id' => $byEmail[$invoice->email]]));

        Prescription::whereNull('patient_user_id')->get()
            ->each(function (Prescription $prescription) {
                $userId = $prescription->appointment?->user_id;
                if ($userId) {
                    $prescription->update(['patient_user_id' => $userId]);
                }
            });
    }

    /**
     * Clinic register: a patient record for every demo patient account
     * plus a few walk-ins who never registered online.
     */
    private function seedPatients(array $users): void
    {
        foreach ($users['patients'] as $i => $user) {
            Patient::firstOrCreate(
                ['phone' => $user->phone],
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'gender' => $user->gender,
                    'date_of_birth' => Carbon::today()->subYears(23 + $i * 4)->subDays($i * 9)->toDateString(),
                    'blood_group' => $user->blood_group,
                    'address' => $user->address,
                ]
            );
        }

        $walkIns = [
            ['Sultana Razia', '01711-234567', 'female', '1988-03-14', 'B+', 'Mirpur 10, Dhaka'],
            ['Jashim Uddin', '01711-987654', 'male', '1979-11-02', 'O+', 'Uttara Sector 7, Dhaka'],
            ['Rokeya Sultana', '01712-456789', 'female', '1995-06-21', 'A+', 'Mohammadpur, Dhaka'],
            ['Nazmul Huda', '01713-654321', 'male', '1968-01-30', 'AB+', 'Old Dhaka, Dhaka'],
        ];

        foreach ($walkIns as [$name, $phone, $gender, $dob, $blood, $address]) {
            Patient::firstOrCreate(
                ['phone' => $phone],
                ['name' => $name, 'gender' => $gender, 'date_of_birth' => $dob, 'blood_group' => $blood, 'address' => $address]
            );
        }
    }

    /**
     * Each doctor works five days a week; the weekly grid feeds the
     * booking flow's open-slot calculation.
     */
    private function seedDoctorSchedules(Collection $doctors, Collection $slots): void
    {
        $slotIds = $slots->pluck('id')->values();
        if ($slotIds->isEmpty()) {
            return;
        }

        $evenDays = [6, 0, 1, 2, 3]; // Saturday – Wednesday
        $oddDays = [0, 1, 2, 3, 4];  // Sunday – Thursday

        foreach ($doctors as $i => $doctor) {
            $days = $i % 2 === 0 ? $evenDays : $oddDays;
            $picks = $i % 2 === 0 ? $slotIds->take(6) : $slotIds->slice(1)->take(5);

            foreach ($days as $weekday) {
                foreach ($picks as $slotId) {
                    DoctorSchedule::firstOrCreate([
                        'doctor_id' => $doctor->id,
                        'weekday' => $weekday,
                        'time_slot_id' => $slotId,
                    ]);
                }
            }
        }
    }

    /**
     * A near-future leave day per doctor, so roster and booking screens
     * show off-day handling.
     */
    private function seedDoctorOffDays(Collection $doctors): void
    {
        $reasons = ['Conference', 'Personal leave', 'Training workshop'];

        foreach ($doctors as $i => $doctor) {
            DoctorOffDay::firstOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'date' => Carbon::today()->addDays(2 + $i * 2)->toDateString(),
                ],
                ['reason' => $reasons[$i % count($reasons)]]
            );
        }
    }

    /**
     * Downloadable PDF reports for the completed lab orders, so the
     * reports panel is never empty.
     */
    private function seedLabReports(array $users): void
    {
        $orders = LabOrder::with('items.test')
            ->whereDoesntHave('reports')
            ->orderBy('id')
            ->take(5)
            ->get();

        foreach ($orders as $order) {
            LabReport::firstOrCreate(
                ['lab_order_id' => $order->id, 'report_name' => 'Lab Report #'.$order->id],
                [
                    'file_path' => $this->storeDemoReportFile($order),
                    'uploaded_by' => $users['admin']->id,
                    'notes' => 'Verified by the duty pathologist.',
                ]
            );
        }
    }

    /**
     * Write a minimal valid PDF report file for a lab order.
     */
    private function storeDemoReportFile(LabOrder $order): string
    {
        $path = 'lab_reports/demo-report-'.$order->id.'.pdf';

        if (Storage::disk('local')->exists($path)) {
            return $path;
        }

        $lines = [
            'MediCare Hospital - Diagnostics Laboratory',
            '------------------------------------------',
            'Report No : LAB-'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
            'Patient   : '.$order->patient_name,
            'Referred By: '.($order->doctor?->name ?? 'General Physician'),
            'Report Date: '.now()->format('d M Y'),
            '',
            'Tests Performed:',
            $order->items
                ->map(fn ($item) => '- '.($item->test?->name ?? 'Test').'  =>  '.($item->result ?? 'Within normal limits'))
                ->implode("\n"),
            '',
            'All findings were verified by the duty pathologist. Please',
            'discuss these results with your physician before changing',
            'any medication.',
        ];

        Storage::disk('local')->put($path, $this->buildPdf($lines));

        return $path;
    }

    /**
     * Build a minimal single-page PDF (Helvetica text lines).
     */
    private function buildPdf(array $lines): string
    {
        $text = '';
        $y = 760;

        foreach ($lines as $line) {
            $line = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $text .= "BT /F1 12 Tf 60 {$y} Td ({$line}) Tj ET\n";
            $y -= 22;
        }

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($text)." >>\nstream\n".$text."endstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $index => $object) {
            $offsets[$index + 1] = strlen($pdf);
            $pdf .= ($index + 1).' 0 obj'."\n".$object."\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }
        $pdf .= 'trailer'."\n".'<< /Size '.(count($objects) + 1).' /Root 1 0 R >>'."\n".'startxref'."\n".$xrefPos."\n%%EOF";

        return $pdf;
    }

    /**
     * Blood bank: fulfil requests end-to-end with issue records and
     * matching donation statuses, so the issues screen has real data.
     */
    private function seedBloodIssues(array $users, Collection $doctors): void
    {
        $groups = BloodGroup::orderBy('id')->get()->keyBy('name');
        if ($groups->isEmpty()) {
            return;
        }

        $rows = [
            ['fulfilled', 'urgent', 0, 'O+', 'Surgery'],
            ['approved', 'normal', 1, 'B+', 'Maternity'],
            ['fulfilled', 'emergency', 2, 'A+', 'Emergency'],
        ];

        foreach ($rows as $i => [$status, $urgency, $patientIndex, $groupName, $department]) {
            $patient = $users['patients'][$patientIndex];
            $group = $groups->get($groupName) ?? $groups->first();

            $request = BloodRequest::firstOrCreate(
                ['patient_id' => $patient->id, 'blood_group_id' => $group->id, 'status' => $status],
                [
                    'quantity' => 900, 'unit' => 'ml',
                    'required_date' => Carbon::today()->subDays(2 + $i)->toDateString(),
                    'urgency' => $urgency, 'department' => $department,
                    'reason' => 'Planned transfusion during treatment',
                    'doctor_id' => $doctors[$i % $doctors->count()]->id,
                    'requested_by' => $doctors[$i % $doctors->count()]->user_id,
                ]
            );

            $donation = BloodDonation::where('blood_group_id', $group->id)
                ->where('status', BloodDonation::STATUS_AVAILABLE)
                ->orderBy('id')
                ->first();

            if ($donation) {
                $donation->update([
                    'status' => $status === 'fulfilled' ? BloodDonation::STATUS_ISSUED : BloodDonation::STATUS_RESERVED,
                    'reserved_for_request_id' => $request->id,
                ]);
            }

            if ($status === 'fulfilled') {
                BloodIssue::firstOrCreate(
                    ['request_id' => $request->id],
                    [
                        'patient_id' => $patient->id,
                        'blood_group_id' => $group->id,
                        'donation_id' => $donation?->id,
                        'quantity' => 450, 'unit' => 'ml',
                        'issue_date' => Carbon::today()->subDays(2 + $i)->toDateString(),
                        'issued_by' => $users['admin']->id,
                        'receiver_name' => $patient->name,
                        'receiver_phone' => $patient->phone,
                        'notes' => 'Cross-matched and issued at the blood bank counter.',
                    ]
                );
            }
        }
    }

    /**
     * Pharmacy shelf: real medicine names with stock spread from healthy
     * to low, plus one batch nearing expiry.
     */
    private function seedMedicines(): void
    {
        $medicines = [
            ['Napa 500mg', 'Paracetamol', 'tablet', 480, 0.80, 60, 720],
            ['Napa Extend 665mg', 'Paracetamol (ER)', 'tablet', 240, 1.50, 50, 540],
            ['Seclo 20mg', 'Omeprazole', 'capsule', 310, 6.00, 40, 660],
            ['Pantonix 40mg', 'Pantoprazole', 'tablet', 145, 9.00, 30, 480],
            ['Monas 10mg', 'Montelukast', 'tablet', 180, 12.00, 30, 420],
            ['Alatrol 10mg', 'Cetirizine', 'tablet', 520, 2.50, 60, 700],
            ['Azithral 500mg', 'Azithromycin', 'tablet', 85, 32.00, 25, 400],
            ['Amoxil 500mg', 'Amoxicillin', 'capsule', 260, 8.50, 40, 360],
            ['Fexo 120mg', 'Fexofenadine', 'tablet', 210, 10.00, 30, 520],
            ['Ventolin Inhaler', 'Salbutamol', 'inhaler', 22, 420.00, 15, 300],
            ['Mixtard 30/70', 'Insulin (human)', 'vial', 14, 950.00, 12, 240],
            ['D-Rise 20000IU', 'Cholecalciferol', 'capsule', 340, 25.00, 25, 600],
            ['Bizoran 80mg', 'Telmisartan', 'tablet', 175, 14.00, 30, 450],
            ['Comet 500mg', 'Metformin', 'tablet', 400, 3.00, 50, 540],
            ['Ace Syrup', 'Paracetamol (syrup)', 'bottle', 65, 85.00, 20, 300],
            ['Alcet Syrup', 'Cetirizine (syrup)', 'bottle', 48, 75.00, 15, 45],
        ];

        foreach ($medicines as [$name, $generic, $unit, $stock, $price, $threshold, $expiryDays]) {
            Medicine::firstOrCreate(
                ['name' => $name],
                [
                    'generic_name' => $generic,
                    'unit' => $unit,
                    'stock_quantity' => $stock,
                    'unit_price' => $price,
                    'low_stock_threshold' => $threshold,
                    'expiry_date' => Carbon::today()->addDays($expiryDays)->toDateString(),
                ]
            );
        }
    }

    /**
     * Vaccination register: mixed completed, upcoming and missed doses
     * for the demo children.
     */
    private function seedVaccinations(array $users): void
    {
        $patients = Patient::whereIn('phone', collect($users['patients'])->pluck('phone'))
            ->get()
            ->keyBy('phone');

        $records = [
            // [child, vaccine, dose, given days ago, due in days, status]
            ['Aayan Rahman', 'BCG', 1, 240, null, Vaccination::STATUS_COMPLETED],
            ['Aayan Rahman', 'Pentavalent', 1, 220, null, Vaccination::STATUS_COMPLETED],
            ['Aayan Rahman', 'Measles-Rubella', 1, 60, 25, Vaccination::STATUS_SCHEDULED],
            ['Mim Akter', 'BCG', 1, 180, null, Vaccination::STATUS_COMPLETED],
            ['Mim Akter', 'Pentavalent', 2, 140, null, Vaccination::STATUS_COMPLETED],
            ['Mim Akter', 'OPV', 3, 90, null, Vaccination::STATUS_MISSED],
            ['Zayan Hasan', 'BCG', 1, 120, null, Vaccination::STATUS_COMPLETED],
            ['Zayan Hasan', 'Pentavalent', 1, 95, null, Vaccination::STATUS_COMPLETED],
            ['Zayan Hasan', 'PCV', 2, null, 35, Vaccination::STATUS_SCHEDULED],
            ['Pihu Das', 'BCG', 1, 60, null, Vaccination::STATUS_COMPLETED],
            ['Pihu Das', 'Pentavalent', 1, null, 10, Vaccination::STATUS_SCHEDULED],
            ['Nabila Islam', 'Measles-Rubella', 1, 20, null, Vaccination::STATUS_COMPLETED],
            ['Nabila Islam', 'Vitamin A', 1, 20, null, Vaccination::STATUS_COMPLETED],
            ['Arham Khan', 'BCG', 1, 10, null, Vaccination::STATUS_COMPLETED],
            ['Arham Khan', 'OPV', 1, null, 22, Vaccination::STATUS_SCHEDULED],
        ];

        foreach ($records as $i => [$child, $vaccine, $dose, $givenDaysAgo, $dueInDays, $status]) {
            $guardian = $users['patients'][$i % count($users['patients'])];

            Vaccination::firstOrCreate(
                ['child_name' => $child, 'vaccine_name' => $vaccine, 'dose_number' => $dose],
                [
                    'user_id' => $guardian->id,
                    'patient_id' => $patients->get($guardian->phone)?->id,
                    'date_given' => $givenDaysAgo !== null ? Carbon::today()->subDays($givenDaysAgo)->toDateString() : null,
                    'next_due_date' => $dueInDays !== null
                        ? Carbon::today()->addDays($dueInDays)->toDateString()
                        : ($status === Vaccination::STATUS_COMPLETED ? Carbon::today()->addDays(30 + $i)->toDateString() : null),
                    'administered_by' => $i % 2 === 0 ? 'Nurse Farzana Akter' : 'Sr. Nurse Rokeya Parvin',
                    'notes' => $status === Vaccination::STATUS_MISSED ? 'Guardian did not attend; recall SMS sent.' : null,
                    'status' => $status,
                    'created_by' => $users['admin']->id,
                ]
            );
        }
    }

    /**
     * In-patient module: four wards with rooms and beds, part occupied.
     */
    private function seedBeds(array $users): void
    {
        $wards = [
            ['General Ward', 'General admission beds with shared facilities and 24/7 nursing.'],
            ['Intensive Care Unit', 'Critical care beds with ventilator and monitor support.'],
            ['Maternity Ward', 'Labour rooms, post-natal beds and newborn care.'],
            ['Pediatric Ward', 'Child-friendly beds with play area and guardian seating.'],
        ];

        $rooms = [
            'General Ward' => [['101', 'General'], ['102', 'General'], ['103', 'Cabin']],
            'Intensive Care Unit' => [['ICU-01', 'ICU'], ['ICU-02', 'ICU']],
            'Maternity Ward' => [['201', 'Maternity'], ['202', 'Cabin']],
            'Pediatric Ward' => [['301', 'Pediatric']],
        ];

        $occupancy = [
            '101' => [1, 1, 1, 0, 0, 0],
            '102' => [0, 1, 0, 0, 0, 0],
            '103' => [1, 0, 0, 2],
            'ICU-01' => [1, 1, 1, 0],
            'ICU-02' => [1, 1, 0, 0],
            '201' => [1, 0, 0, 0],
            '202' => [0, 0, 0, 0],
            '301' => [1, 1, 0, 0, 0, 2],
        ];

        $patientIds = collect($users['patients'])->pluck('id')->values();
        $cursor = 0;

        foreach ($wards as [$name, $description]) {
            $ward = Ward::firstOrCreate(['name' => $name], ['description' => $description]);

            foreach ($rooms[$name] as [$roomNumber, $type]) {
                $room = Room::firstOrCreate(
                    ['ward_id' => $ward->id, 'room_number' => $roomNumber],
                    ['room_type' => $type]
                );

                foreach ($occupancy[$roomNumber] as $index => $status) {
                    $occupied = $status === Bed::STATUS_OCCUPIED;

                    Bed::firstOrCreate(
                        ['room_id' => $room->id, 'bed_number' => (string) ($index + 1)],
                        [
                            'status' => $status,
                            'current_patient_id' => $occupied ? $patientIds[$cursor++ % $patientIds->count()] : null,
                            'admitted_at' => $occupied ? now()->subHours(6 + $index * 9) : null,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Ambulance dispatch log with requests in every stage.
     */
    private function seedAmbulanceRequests(array $users): void
    {
        $rows = [
            ['Rahim Uddin', '01711-450120', 'House 12, Road 5, Dhanmondi', 'Cardiac emergency', 0, 0],
            ['Sultana Razia', '01711-774512', 'Block C, Mirpur 10', 'Road accident', 1, 0],
            ['Jashim Uddin', '01712-309866', 'Sector 7, Uttara', 'Breathing difficulty', 2, 1],
            ['Fatema Begum', '01713-118270', 'Mohammadpur Bus Stand', 'Pregnancy complication', 2, 2],
            ['Mohan Das', '01714-662901', 'Shahbagh, Dhaka', 'High fever with convulsion', 2, 3],
            ['Nusrat Jahan', '01715-887234', 'Bashundhara R/A', 'Fall injury', 3, 4],
            ['Guest Caller', '01716-453398', 'Jatrabari Chowrasta', 'Stroke symptoms', 2, 5],
            ['Habib Rahman', '01717-221490', 'Green Road, Dhaka', 'Chest pain', 1, 0],
        ];

        foreach ($rows as $i => [$name, $phone, $pickup, $type, $status, $patientIndex]) {
            $patient = $users['patients'][$patientIndex];

            $request = AmbulanceRequest::firstOrCreate(
                ['requester_phone' => $phone, 'pickup_address' => $pickup],
                [
                    'user_id' => $name === 'Guest Caller' ? null : $patient->id,
                    'requester_name' => $name,
                    'destination' => 'MediCare Hospital, Green Road',
                    'emergency_type' => $type,
                    'status' => $status,
                ]
            );

            if ($request->wasRecentlyCreated) {
                $request->created_at = now()->subHours(4 + $i * 7);
                $request->updated_at = now()->subHours(2 + $i * 7);
                $request->save();
            }
        }
    }

    /**
     * Telehealth rooms for upcoming consultations (demo links, ready state).
     */
    private function seedZoomMeetings(): void
    {
        if (ZoomMeeting::count() > 0) {
            return;
        }

        $upcoming = Appointment::where('status', 1)
            ->whereDate('appointment_date', '>=', Carbon::today())
            ->orderBy('appointment_date')
            ->take(4)
            ->get();

        if ($upcoming->isEmpty()) {
            $upcoming = Appointment::where('status', 1)->orderByDesc('id')->take(3)->get();
        }

        foreach ($upcoming as $i => $appointment) {
            $number = '845'.str_pad((string) (1234567 + $i * 111111), 7, '0', STR_PAD_LEFT);

            ZoomMeeting::firstOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'zoom_meeting_id' => $number,
                    'join_url' => 'https://zoom.us/j/'.$number.'?pwd=demodemo',
                    'start_url' => 'https://zoom.us/s/'.$number.'?zak=demo-host-token',
                    'sync_status' => 'ready',
                    'patient_notified_at' => $i === 0 ? now()->subDay() : null,
                    'patient_notification_key' => $i === 0 ? 'demo-'.$appointment->id : null,
                ]
            );
        }
    }

    /**
     * Patient reviews for recently completed appointments, one awaiting
     * moderation so the admin queue is not empty.
     */
    private function seedReviews(): void
    {
        if (DoctorReview::count() > 0) {
            return;
        }

        $comments = [
            'Very patient doctor, explained everything clearly and the follow-up plan worked.',
            'Waited less than fifteen minutes. Diagnosis and medicine were spot on.',
            'Kind and thorough — my child was not afraid at all. Highly recommended.',
            'The doctor listened to my full history before prescribing. Very satisfied.',
            'Clear advice on diet and exercise along with the medicine. Thank you.',
            'Second visit for my mother — consistent, caring and never rushed.',
            'Got an appointment the same week and the treatment relieved my pain quickly.',
            'Professional and friendly. The online follow-up saved me a trip.',
            'Explained the lab reports line by line. Worth every taka.',
            'Good consultation, though the waiting room was a little busy that evening.',
        ];
        $ratings = [5, 5, 5, 4, 5, 5, 4, 5, 5, 4];

        $completed = Appointment::where('status', 2)
            ->orderByDesc('appointment_date')
            ->take(count($comments))
            ->get();

        foreach ($completed as $i => $appointment) {
            DoctorReview::firstOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'doctor_id' => $appointment->doctor_id,
                    'user_id' => $appointment->user_id,
                    'rating' => $ratings[$i % count($ratings)],
                    'comment' => $comments[$i],
                    'status' => $i === 2 ? 0 : 1,
                ]
            );
        }
    }

    /**
     * Front-end FAQ accordion.
     */
    private function seedFaqs(): void
    {
        $faqs = [
            ['How do I book an appointment?', 'Book online in under a minute: choose your doctor, pick a free slot and confirm with your phone number. You will receive a confirmation immediately, and our front desk calls to reconfirm the day before.'],
            ['Do I need an appointment for the emergency department?', 'No. The emergency unit is open 24/7 and patients are seen in order of medical priority, not arrival. For ambulance support, call our hotline and we will prepare a bed before you arrive.'],
            ['Can I collect my lab report online?', 'Yes. Most routine reports are ready the same day and appear in your patient dashboard, where you can download them. Emergency and critical results are phoned to your doctor within the hour.'],
            ['Is there a blood bank on site?', 'Yes, our blood bank stocks all groups with screened units and component separation. Walk-in donors are welcome from 9 AM to 8 PM, and we issue units round the clock for emergencies.'],
            ['Which insurance and corporate panels do you accept?', 'We work with most major insurance providers and corporate health panels. Bring your card or authorisation letter, and our billing desk will process the paperwork while you wait.'],
            ['What should I bring to my first visit?', 'A photo ID, your phone (for confirmation), any previous prescriptions or reports, and a list of the medicines you currently take. Arrive ten minutes early for registration.'],
            ['Do you offer home sample collection?', 'Yes, across Dhaka city. Book through the lab counter or by phone before 10 AM, and a phlebotomist will visit the same day. Reports arrive digitally as usual.'],
            ['How do I get my prescription after an online consultation?', 'After the consultation your prescription is issued digitally and you can collect the printed copy from our pharmacy counter, or have medicines dispensed while you wait.'],
        ];

        foreach ($faqs as $i => [$question, $answer]) {
            Faq::firstOrCreate(
                ['question' => $question],
                ['answer' => $answer, 'sort_order' => $i, 'status' => 1]
            );
        }
    }

    /**
     * Hospital gallery using the shipped demo photography.
     */
    private function seedGallery(): void
    {
        $images = [
            ['sliders/slider-1.jpg', 'Reception and patient waiting lounge'],
            ['sliders/slider-2.jpg', 'Modern consultation rooms'],
            ['sliders/slider-3.png', 'Emergency response team on standby'],
            ['home/about-1.jpg', 'Laboratory diagnostics unit'],
            ['home/about-2.jpg', 'Specialist dental care'],
            ['home/about-3.jpeg', 'Eye clinic examination'],
            ['about/about-1.jpg', 'Inpatient wing corridor'],
            ['about/about-2.jpg', 'Physiotherapy and rehabilitation'],
            ['service/emergency.jpg', '24/7 emergency department'],
            ['blogs/blog-1.jpeg', 'Community health awareness program'],
        ];

        foreach ($images as $i => [$image, $caption]) {
            GalleryImage::firstOrCreate(
                ['image' => $image],
                ['caption' => $caption, 'sort_order' => $i, 'status' => 1]
            );
        }
    }

    /**
     * In-app notifications for staff and one patient, produced through
     * the same notifier services the live app uses.
     */
    private function seedNotifications(): void
    {
        if (DB::table('notifications')->count() > 0) {
            return;
        }

        Appointment::orderByDesc('id')->take(3)->get()
            ->each(fn (Appointment $appointment) => StaffNotifier::appointmentBooked($appointment));

        if (Module::enabled('lab')) {
            LabOrder::orderByDesc('id')->take(2)->get()
                ->each(fn (LabOrder $order) => StaffNotifier::labResultReady($order));
        }

        if (Module::enabled('ambulance') && ($request = AmbulanceRequest::orderByDesc('id')->first())) {
            StaffNotifier::ambulanceRequested($request);
        }

        if ($appointment = Appointment::whereNotNull('user_id')->orderByDesc('id')->first()) {
            PatientNotifier::appointmentBooked($appointment);
        }

        if (Module::enabled('lab') && ($order = LabOrder::whereNotNull('user_id')->orderByDesc('id')->first())) {
            PatientNotifier::labResultReady($order);
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
            'services_subtitle' => 'Our Services',
            'services_title' => 'We Care Our Patients',
            'blog_subtitle' => 'Our Blog',
            'blog_title' => 'Recent Articles and News',
            'brands_subtitle' => 'Sister Concerns',
            'brands_title' => 'Our Brands',
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

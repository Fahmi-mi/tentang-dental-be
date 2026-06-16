<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\AdminNotification;
use App\Models\Article;
use App\Models\Doctor;
use App\Models\ExaminationImage;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Patient;
use App\Models\PatientDentalHistory;
use App\Models\PatientMedicalHistory;
use App\Models\Promo;
use App\Models\Reservation;
use App\Models\Rontgen;
use App\Models\Service;
use App\Models\Tag;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $registrationAdmin = Admin::updateOrCreate(
            ['email' => 'admin@tentangdental.com'],
            [
                'name' => 'Admin Registration',
                'password' => Hash::make('password'),
                'role' => 'registration',
                'profile_image' => null,
            ]
        );

        $rontgenAdmin = Admin::updateOrCreate(
            ['email' => 'rontgen@tentangdental.com'],
            [
                'name' => 'Admin Rontgen',
                'password' => Hash::make('password'),
                'role' => 'rontgen',
                'profile_image' => null,
            ]
        );

        foreach ([
            ['tag_name' => 'caries', 'detail' => 'Karies gigi memerlukan tindak lanjut.'],
            ['tag_name' => 'routine exam', 'detail' => 'Pemeriksaan rutin berkala.'],
            ['tag_name' => 'follow up', 'detail' => 'Kontrol lanjutan pasca tindakan.'],
        ] as $tag) {
            Tag::updateOrCreate(['tag_name' => $tag['tag_name']], $tag);
        }

        Doctor::factory(4)->create();
        Service::factory(8)->create();
        Promo::factory(5)->create();
        Faq::factory(8)->create();
        Testimonial::factory(8)->create();
        Gallery::factory(8)->create();

        Article::factory(8)->create([
            'admin_id' => $registrationAdmin->id,
        ]);

        $patients = Patient::factory(12)->create();

        foreach ($patients as $patient) {
            PatientMedicalHistory::updateOrCreate(
                ['patient_id' => $patient->id],
                [
                    'has_allergy' => (bool) random_int(0, 1),
                    'allergy_detail' => 'Alergi dingin',
                    'has_systemic_disease' => (bool) random_int(0, 1),
                    'systemic_disease_detail' => 'Hipertensi ringan',
                    'undergoing_treatment' => (bool) random_int(0, 1),
                    'treatment_detail' => 'Kontrol rutin',
                    'ever_hospitalized' => (bool) random_int(0, 1),
                    'hospitalized_reason' => 'Demam berdarah',
                    'smoking_or_alcohol' => false,
                    'created_at' => now(),
                ]
            );

            PatientDentalHistory::updateOrCreate(
                ['patient_id' => $patient->id],
                [
                    'frequent_tooth_pain' => (bool) random_int(0, 1),
                    'tooth_pain_detail' => 'Nyeri saat malam',
                    'bleeding_gums' => (bool) random_int(0, 1),
                    'ever_dental_treatment' => true,
                    'dental_treatment_detail' => 'Tambal gigi',
                    'brushing_frequency' => '2',
                    'use_floss_or_mouthwash' => true,
                    'bad_habits' => false,
                    'bad_habits_detail' => null,
                    'ever_braces' => false,
                    'braces_years' => null,
                    'root_canal_treatment' => false,
                    'root_canal_detail' => null,
                    'dentures' => false,
                    'routine_checkup' => true,
                    'dental_checkup_frequency' => '6_months',
                    'doctor_notes' => 'Perlu evaluasi plak berkala.',
                    'created_at' => now(),
                ]
            );
        }

        $doctors = Doctor::query()->inRandomOrder()->get();
        $services = Service::query()->inRandomOrder()->get();
        $tags = Tag::query()->get();

        foreach ($patients->take(10) as $index => $patient) {
            $doctor = $doctors->random();

            $reservation = Reservation::create([
                'patient_id' => $patient->id,
                'patient_category' => $index % 2 === 0 ? 'new' : 'existing',
                'doctor_id' => $doctor->id,
                'complain' => 'Keluhan gigi sensitif dan nyeri ringan',
                'reservation_date' => now()->addDays($index + 1)->format('Y-m-d'),
                'birth_date' => optional($patient->birth_date)->format('Y-m-d'),
                'age' => $patient->age,
                'appointment_time' => ['08:00:00', '10:00:00', '14:00:00', '16:00:00'][($index % 4)],
                'status' => ['pending', 'validated', 'completed'][($index % 3)],
                'created_at' => now()->subDays(12 - $index),
            ]);

            $reservation->services()->sync(
                $services->random(random_int(1, min(3, $services->count())))->pluck('id')->all()
            );

            if ($index % 2 === 0) {
                $rontgen = Rontgen::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'detail' => 'Pemeriksaan rontgen panoramik untuk evaluasi akar gigi.',
                ]);

                $imageCount = random_int(1, 3);
                for ($i = 1; $i <= $imageCount; $i++) {
                    ExaminationImage::create([
                        'rontgen_id' => $rontgen->id,
                        'image_path' => 'seed_rontgen_' . $rontgen->id . '_' . $i . '.jpg',
                        'image_type' => 'image/jpeg',
                    ]);
                }

                $rontgen->tags()->sync(
                    $tags->random(random_int(1, min(2, $tags->count())))->pluck('id')->all()
                );

                AdminNotification::create([
                    'admin_id' => $rontgenAdmin->id,
                    'title' => 'Xray Uploaded',
                    'message' => 'Rontgen baru untuk pasien ' . $patient->name . ' telah diunggah.',
                    'type' => 'xray_uploaded',
                    'is_read' => false,
                ]);
            }

            AdminNotification::create([
                'admin_id' => $registrationAdmin->id,
                'title' => 'Patient Status Updated',
                'message' => 'Status reservasi pasien ' . $patient->name . ' diperbarui menjadi ' . $reservation->status . '.',
                'type' => 'patient status updated',
                'is_read' => $index % 3 === 0,
            ]);
        }
    }
}

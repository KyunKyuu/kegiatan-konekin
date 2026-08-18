<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Person;
use App\Models\Activity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Users
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@konekin.space',
            'password' => Hash::make('Akuganteng'),
            'is_admin' => true,
        ]);

        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        // 2. Create People
        $peopleNames = [
            'Budi Santoso', 'Siti Aminah', 'Rudi Hermawan', 'Dewi Lestari',
            'Ahmad Fauzi', 'Indah Permata', 'Agus Salim', 'Lani Wijaya',
            'Eko Prasetyo', 'Diana Fitri'
        ];

        $people = [];
        foreach ($peopleNames as $name) {
            $people[] = Person::create(['name' => $name]);
        }

        // 3. Categories and Descriptions
        $categoryData = [
            ['name' => 'Rapat', 'color' => 'purple'],
            ['name' => 'Kerja Bakti', 'color' => 'blue'],
            ['name' => 'Sosialisasi', 'color' => 'green'],
            ['name' => 'Seminar', 'color' => 'orange'],
            ['name' => 'Evaluasi', 'color' => 'red'],
            ['name' => 'Outing', 'color' => 'cyan'],
        ];

        foreach ($categoryData as $cat) {
            \App\Models\Category::create($cat);
        }

        $categories = collect($categoryData)->pluck('name')->toArray();
        
        $activityTemplates = [
            'Rapat' => [
                'Rapat Koordinasi Bulanan',
                'Rapat Tinjauan Manajemen',
                'Rapat Kerja Divisi IT',
                'Rapat Evaluasi Program Kerja',
            ],
            'Kerja Bakti' => [
                'Kerja Bakti Bersih Lingkungan Kantor',
                'Penataan Ulang Ruang Arsip',
                'Gotong Royong Penghijauan Halaman',
            ],
            'Sosialisasi' => [
                'Sosialisasi SOP Keamanan Informasi',
                'Sosialisasi Kebijakan Baru Perusahaan',
                'Sosialisasi Penggunaan Sistem Absensi Baru',
            ],
            'Seminar' => [
                'Seminar Perkembangan Teknologi AI',
                'Seminar Kesehatan Mental di Tempat Kerja',
                'Seminar Manajemen Waktu Efektif',
            ],
            'Evaluasi' => [
                'Evaluasi Kinerja Kuartal I',
                'Evaluasi Proyek Pengembangan Website',
                'Evaluasi Kepuasan Pelanggan',
            ],
            'Outing' => [
                'Outing Bersama Staff',
                'Futsal Rutin Karyawan',
                'Gathering Akhir Pekan',
            ]
        ];

        // 4. Create Activities across 3 months: Last Month, This Month, Next Month
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfNextMonth = Carbon::now()->addMonth()->endOfMonth();

        $currentDate = $startOfLastMonth->copy();
        
        // Let's seed around 40 activities
        $totalActivities = 45;
        for ($i = 0; $i < $totalActivities; $i++) {
            // Random date between last month and next month
            $randomDays = rand(0, 90);
            $activityDate = $startOfLastMonth->copy()->addDays($randomDays);
            
            $category = $categories[array_rand($categories)];
            $templates = $activityTemplates[$category];
            $description = $templates[array_rand($templates)] . ' ' . $activityDate->format('Y-m-d');
            
            // Generate random start & end time for ~80% of the activities
            $hasTime = rand(0, 4) > 0;
            $startTime = null;
            $endTime = null;
            if ($hasTime) {
                $startHour = rand(7, 17);
                $startMin = rand(0, 1) === 0 ? '00' : '30';
                $duration = rand(1, 2); // 1 to 2 hours
                $endHour = $startHour + $duration;
                $endMin = $startMin;
                
                $startTime = sprintf('%02d:%s:00', $startHour, $startMin);
                $endTime = sprintf('%02d:%s:00', $endHour, $endMin);
            }

            // Create activity
            $activity = Activity::create([
                'category' => $category,
                'description' => $description,
                'activity_date' => $activityDate->format('Y-m-d'),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'user_id' => rand(0, 1) === 0 ? $admin->id : $user->id,
            ]);

            // Random PICs (1 to 2 people)
            $picCount = rand(1, 2);
            $shuffledPics = $people;
            shuffle($shuffledPics);
            $pics = array_slice($shuffledPics, 0, $picCount);
            $picIds = collect($pics)->pluck('id')->toArray();
            $activity->pics()->sync($picIds);

            // Random participants (1 to 5 people)
            $participantCount = rand(1, 5);
            $shuffledPeople = $people;
            shuffle($shuffledPeople);
            $participants = array_slice($shuffledPeople, 0, $participantCount);
            
            $participantIds = collect($participants)->pluck('id')->toArray();
            $activity->participants()->sync($participantIds);
        }

        // 4. Seed Capstones & Milestones for Monitoring Module
        $capstone1 = \App\Models\Milestone::create([
            'title' => 'Tahap 1: Inisiasi & Riset Kebutuhan',
            'description' => 'Analisis sistem awal dan penyusunan dokumen blueprint arsitektur.',
            'target_date' => Carbon::today()->addDays(5),
            'status' => 'completed',
            'color' => 'theme-purple',
            'order' => 1,
            'created_by' => $admin->id,
        ]);
        $capstone1->people()->sync([$people[0]->id, $people[1]->id, $people[2]->id]);

        \App\Models\Milestone::create([
            'parent_id' => $capstone1->id,
            'title' => 'Sub 1.1: Survey User & Focus Group',
            'target_date' => Carbon::today()->addDays(2),
            'status' => 'completed',
            'color' => 'theme-purple',
            'order' => 1,
        ])->people()->sync([$people[0]->id, $people[1]->id]);

        \App\Models\Milestone::create([
            'parent_id' => $capstone1->id,
            'title' => 'Sub 1.2: Penyusunan Wireframe UI',
            'target_date' => Carbon::today()->addDays(4),
            'status' => 'completed',
            'color' => 'theme-purple',
            'order' => 2,
        ])->people()->sync([$people[2]->id]);

        $capstone2 = \App\Models\Milestone::create([
            'title' => 'Tahap 2: Pengembangan Core Module',
            'description' => 'Implementasi backend API Laravel, modul Kalender, dan modul Monitoring Penggaris.',
            'target_date' => Carbon::today()->addDays(15),
            'status' => 'in_progress',
            'color' => 'theme-blue',
            'order' => 2,
            'created_by' => $admin->id,
        ]);
        $capstone2->people()->sync([$people[3]->id, $people[4]->id, $people[5]->id, $people[6]->id]);

        \App\Models\Milestone::create([
            'parent_id' => $capstone2->id,
            'title' => 'Sub 2.1: Skema Database & Migrasi',
            'target_date' => Carbon::today()->addDays(8),
            'status' => 'completed',
            'color' => 'theme-blue',
            'order' => 1,
        ])->people()->sync([$people[3]->id]);

        \App\Models\Milestone::create([
            'parent_id' => $capstone2->id,
            'title' => 'Sub 2.2: Fitur Timeline Ruler UI',
            'target_date' => Carbon::today()->addDays(12),
            'status' => 'in_progress',
            'color' => 'theme-blue',
            'order' => 2,
        ])->people()->sync([$people[4]->id, $people[5]->id]);

        $capstone3 = \App\Models\Milestone::create([
            'title' => 'Tahap 3: Pengujian & Peluncuran',
            'description' => 'User acceptance test, audit keamanan, dan rilis versi produksi.',
            'target_date' => Carbon::today()->addDays(25),
            'status' => 'pending',
            'color' => 'theme-green',
            'order' => 3,
            'created_by' => $admin->id,
        ]);
        $capstone3->people()->sync([$people[7]->id, $people[8]->id]);

        // 5. Seed Master Targets
        $mt1 = \App\Models\MasterTarget::create([
            'title' => '++PZ (Sertifikasi Penanggung Jawab)',
            'description' => 'Lulus evaluasi standar penanggung jawab proyek.',
        ]);
        $mt2 = \App\Models\MasterTarget::create([
            'title' => '++01 (Standard Operating Procedure)',
            'description' => 'Memahami dan menjalankan SOP level 1.',
        ]);
        $mt3 = \App\Models\MasterTarget::create([
            'title' => 'Target Sales 10 Client Baru',
            'description' => 'Mencapai target konversi sales bulanan.',
        ]);
        $mt4 = \App\Models\MasterTarget::create([
            'title' => 'Sertifikasi Keaktifan Katim',
            'description' => 'Menyelesaikan pelatihan kepemimpinan tim.',
        ]);

        // Assign Person Targets & Histories to Person #1
        $p1 = $people[0];
        \App\Models\PersonTarget::create([
            'person_id' => $p1->id,
            'master_target_id' => $mt1->id,
            'title' => '++PZ',
            'is_completed' => true,
            'completed_at' => Carbon::now()->subDays(2),
        ]);
        \App\Models\PersonTarget::create([
            'person_id' => $p1->id,
            'master_target_id' => $mt2->id,
            'title' => '++01',
            'is_completed' => false,
        ]);
        \App\Models\PersonTarget::create([
            'person_id' => $p1->id,
            'master_target_id' => $mt3->id,
            'title' => 'Target Sales 10 Client Baru',
            'is_completed' => false,
        ]);

        // Scale History
        \App\Models\PersonScaleHistory::create([
            'person_id' => $p1->id,
            'scale_type' => 'sales',
            'old_value' => 2,
            'new_value' => 4,
            'reason' => 'Peningkatan performa negosiasi dengan klien baru.',
            'changed_by' => $admin->id,
        ]);
        \App\Models\PersonScaleHistory::create([
            'person_id' => $p1->id,
            'scale_type' => 'keaktifan',
            'old_value' => 3,
            'new_value' => 5,
            'reason' => 'Selalu memimpin diskusi dan hadir paling awal.',
            'changed_by' => $admin->id,
        ]);

        // Multi Notes
        \App\Models\PersonNote::create([
            'person_id' => $p1->id,
            'category' => 'Akademik',
            'status_label' => 'Lulus Evaluasi',
            'note' => 'Menyelesaikan evaluasi teori dengan nilai A.',
            'created_by' => $admin->id,
        ]);
        \App\Models\PersonNote::create([
            'person_id' => $p1->id,
            'category' => 'Keuangan',
            'status_label' => 'Lunas',
            'note' => 'Administrasi modul dan iuran organisasi telah lunas.',
            'created_by' => $admin->id,
        ]);
        \App\Models\PersonNote::create([
            'person_id' => $p1->id,
            'category' => 'Kinerja',
            'status_label' => 'Sangat Baik',
            'note' => 'Responsif terhadap tiket permasalahan dan bug.',
            'created_by' => $admin->id,
        ]);
    }
}



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
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
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
    }
}

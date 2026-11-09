<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            DemoUserSeeder::class,
        ]);

        $wira = User::firstOrCreate(
            ['email' => 'wira@student.its.ac.id'],
            [
                'name' => 'Wira Mahasiswa',
                'password' => 'password',
            ]
        );
        $wira->syncRoles(['mahasiswa']);

        $wira->projects()->firstOrCreate(
            ['judul' => 'Asisten Akademik'],
            [
                'deskripsi' => 'Ide proyek agentic ai untuk membantu kegiatan akademik',
                'tema_agent' => 'Ollama',
                'api_key_secure' => null,
            ]
        );

        foreach (range(1, 15) as $number) {
            $student = User::firstOrCreate(
                ['email' => sprintf('week6.mahasiswa.%02d@student.its.ac.id', $number)],
                [
                    'name' => fake()->name(),
                    'password' => 'password',
                ]
            );
            $student->syncRoles(['mahasiswa']);

            $projectsToCreate = max(0, 2 - $student->projects()->count());

            if ($projectsToCreate > 0) {
                $student->projects()->saveMany(
                    Project::factory()->count($projectsToCreate)->make()
                );
            }

            // Minggu 10: proposal dummy Faker untuk demo pencarian, filter, dan pagination admin.
            $proposalsToCreate = max(0, 2 - $student->proposals()->count());

            if ($proposalsToCreate > 0) {
                $student->proposals()->saveMany(
                    Proposal::factory()->count($proposalsToCreate)->make(['user_id' => $student->id])
                );
            }
        }

        $demoLecturer = User::firstOrCreate(
            ['email' => 'dosenpbkk@its.ac.id'],
            [
                'name' => 'Dosen PBKK',
                'password' => 'password',
            ]
        );
        $demoLecturer->syncRoles(['dosen']);

        foreach ([
            [
                'judul' => 'Asisten Riset Agentic AI',
                'deskripsi' => 'Asisten untuk membantu pencarian dan perangkuman referensi penelitian.',
                'tema_agent' => 'Ollama',
            ],
            [
                'judul' => 'Perencana Akademik Cerdas',
                'deskripsi' => 'Agent untuk menyusun rencana studi berdasarkan prasyarat mata kuliah.',
                'tema_agent' => 'OpenAI',
            ],
        ] as $project) {
            $demoLecturer->projects()->firstOrCreate(
                ['judul' => $project['judul']],
                [
                    'deskripsi' => $project['deskripsi'],
                    'tema_agent' => $project['tema_agent'],
                    'api_key_secure' => null,
                ]
            );
        }
    }
}

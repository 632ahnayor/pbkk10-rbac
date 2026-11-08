<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('seeds fifteen students with two projects each and a lecturer portfolio', function () {
    $this->seed();

    $this->assertDatabaseHas('users', [
        'email' => 'andi@example.com',
        'role' => 'mahasiswa',
    ]);

    $students = User::where('email', 'like', 'week6.mahasiswa.%@student.its.ac.id')
        ->orderBy('email')
        ->get();

    expect($students)->toHaveCount(15);

    foreach ($students as $student) {
        expect($student->role)->toBe('mahasiswa')
            ->and($student->projects)->toHaveCount(2)
            ->and($student->projects->every(fn (Project $project) => $project->user_id === $student->id))->toBeTrue();
    }

    $lecturer = User::where('email', 'dosenpbkk@its.ac.id')->firstOrFail();

    expect($lecturer->role)->toBe('dosen')
        ->and($lecturer->projects)->toHaveCount(2)
        ->and(Hash::check('password', $lecturer->password))->toBeTrue();
});

it('can run the database seeder repeatedly without duplicating challenge data', function () {
    $this->seed();
    $this->seed();

    expect(User::where('email', 'like', 'week6.mahasiswa.%@student.its.ac.id')->count())->toBe(15)
        ->and(Project::whereHas('user', fn ($query) => $query->where('email', 'dosenpbkk@its.ac.id'))->count())->toBe(2);

    foreach (User::where('email', 'like', 'week6.mahasiswa.%@student.its.ac.id')->get() as $student) {
        expect($student->projects()->count())->toBe(2);
    }
});

it('shows only the authenticated users projects on the dashboard', function () {
    $owner = User::factory()->create(['role' => 'mahasiswa']);
    $otherUser = User::factory()->create(['role' => 'mahasiswa']);

    $ownerProject = $owner->projects()->create([
        'judul' => 'Project Milik Saya',
        'deskripsi' => 'Hanya pemilik yang boleh melihatnya pada dashboard.',
    ]);
    $otherUser->projects()->create([
        'judul' => 'Project Milik Orang Lain',
        'deskripsi' => 'Tidak boleh tampil pada dashboard pemilik pertama.',
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee($ownerProject->judul)
        ->assertDontSee('Project Milik Orang Lain');
});

it('provides a week six database navigation link for guests and authenticated users', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Minggu 6')
        ->assertSee('Login untuk Demo Database')
        ->assertSee('href="'.route('login').'"', false);

    $user = User::factory()->create(['role' => 'mahasiswa']);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Minggu 6')
        ->assertSee('Demo Database')
        ->assertSee('href="'.route('dashboard').'"', false);
});

it('quick logs into the first week six student from the local login page', function () {
    $this->app['env'] = 'local';
    $this->seed();

    $student = User::where('email', 'week6.mahasiswa.01@student.its.ac.id')->firstOrFail();

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Minggu 6: Mahasiswa 01')
        ->assertSee(route('login.demo', ['account' => 'week6-mahasiswa-01']));

    $this->post(route('login.demo', ['account' => 'week6-mahasiswa-01']), ['_token' => session()->token()])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($student);
});

it('does not allow quick login as a week six student outside the local environment', function () {
    $this->app['env'] = 'testing';

    $this->post(route('login.demo', ['account' => 'week6-mahasiswa-01']))
        ->assertNotFound();

    $this->assertGuest();
});

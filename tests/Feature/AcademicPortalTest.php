<?php

use App\Models\Proposal;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('registers new users as mahasiswa even when a privileged role is submitted', function () {
    $this->post(route('register'), [
        'name' => 'New Student',
        'email' => 'new-student@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'dosen',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $user = User::where('email', 'new-student@example.com')->firstOrFail();
    expect($user->getRoleNames()->all())->toBe(['mahasiswa']);
});

it('seeds the requested demo users with their roles and shared password', function () {
    $this->seed(DemoUserSeeder::class);

    foreach ([
        'andi@example.com' => ['Andi', 'mahasiswa'],
        'budi@example.com' => ['Budi', 'mahasiswa'],
        'asdos@example.com' => ['Asdos', 'asdos'],
        'dosen@example.com' => ['Dosen', 'dosen'],
        'admin@example.com' => ['Admin', 'admin'],
        'staf@example.com' => ['Staf', 'staf'],
    ] as $email => [$name, $role]) {
        $user = User::where('email', $email)->firstOrFail();

        expect($user->name)->toBe($name)
            ->and($user->getRoleNames()->all())->toBe([$role])
            ->and(Hash::check('password', $user->password))->toBeTrue();
    }
});

it('authenticates the seeded users through the Breeze login form', function () {
    $this->seed(DemoUserSeeder::class);

    foreach ([
        'andi' => 'dashboard',
        'budi' => 'dashboard',
        'asdos' => 'admin.dashboard',
        'dosen' => 'admin.dashboard',
    ] as $username => $expectedRoute) {
        $user = User::where('email', $username.'@example.com')->firstOrFail();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route($expectedRoute, absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->post(route('logout'));
        $this->assertGuest();
    }
});

it('protects admin routes from guests and students but allows asdos and dosen', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

    $student = User::factory()->withRole('mahasiswa')->create();
    $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();

    foreach (['asdos', 'dosen'] as $role) {
        $user = User::factory()->withRole($role)->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }
});

it('assigns new proposals to the signed-in student and restricts title edits to their owner', function () {
    $owner = User::factory()->withRole('mahasiswa')->create();
    $otherStudent = User::factory()->withRole('mahasiswa')->create();

    $this->actingAs($owner)
        ->post(route('proposals.store'), [
            'title' => 'AI Study Assistant',
            'description' => 'An assistant for course materials.',
            'user_id' => $otherStudent->id,
        ])
        ->assertRedirect(route('dashboard'));

    $proposal = Proposal::firstOrFail();
    expect($proposal->user_id)->toBe($owner->id);

    $this->get(route('proposals.edit', $proposal))->assertOk();
    $this->patch(route('proposals.update', $proposal), ['title' => 'Updated AI Study Assistant'])
        ->assertRedirect(route('dashboard'));
    expect($proposal->fresh()->title)->toBe('Updated AI Study Assistant');

    $this->actingAs($otherStudent)
        ->patch(route('proposals.update', $proposal), ['title' => 'Unauthorized edit'])
        ->assertForbidden();
});

it('shows the admin panel navigation only to staff and allows staff to record grades', function () {
    $student = User::factory()->withRole('mahasiswa')->create();
    $proposal = Proposal::create([
        'user_id' => $student->id,
        'title' => 'AI Study Assistant',
    ]);

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee(route('admin.dashboard'));

    $asdos = User::factory()->withRole('asdos')->create();
    $this->actingAs($asdos)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(route('admin.dashboard'));

    $this->get(route('admin.grades.index'))->assertOk();

    $this->patch(route('admin.grades.update', $proposal), ['grade' => 92.5])
        ->assertRedirect(route('admin.grades.index'));

    expect((float) $proposal->fresh()->grade)->toBe(92.5);
});

it('keeps the query-parameter local demo separate from authenticated users', function () {
    $this->get(route('local-login'))
        ->assertOk();
    $this->assertGuest();

    $this->get(route('home', ['user' => 'Andi']))
        ->assertOk()
        ->assertSee('Selamat datang, Andi!');
    $this->assertGuest();

    $signedInUser = User::factory()->withRole('mahasiswa')->create(['name' => 'Authenticated Student']);
    $this->actingAs($signedInUser)
        ->get(route('home', ['user' => 'Andi']))
        ->assertSee('Selamat datang, Authenticated Student!');
});

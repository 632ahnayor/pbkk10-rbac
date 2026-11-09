<?php

use App\Models\Proposal;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('limits each student to three proposal submissions per minute', function () {
    $student = User::factory()->withRole('mahasiswa')->create();
    $this->actingAs($student);

    foreach (['Proposal One', 'Proposal Two', 'Proposal Three'] as $title) {
        $this->post(route('proposals.store'), ['title' => $title])
            ->assertRedirect(route('dashboard'));
    }

    $this->post(route('proposals.store'), ['title' => 'Proposal Four'])
        ->assertTooManyRequests();

    expect(Proposal::where('user_id', $student->id)->count())->toBe(3);
});

it('keeps proposal submission limits isolated per student', function () {
    $firstStudent = User::factory()->withRole('mahasiswa')->create();

    $this->actingAs($firstStudent);
    foreach (['First One', 'First Two', 'First Three'] as $title) {
        $this->post(route('proposals.store'), ['title' => $title])
            ->assertRedirect(route('dashboard'));
    }

    $secondStudent = User::factory()->withRole('mahasiswa')->create();
    $this->actingAs($secondStudent)
        ->post(route('proposals.store'), ['title' => 'Second Student Proposal'])
        ->assertRedirect(route('dashboard'));

    expect(Proposal::where('user_id', $secondStudent->id)->count())->toBe(1);
});

it('shows quick login buttons only in the local environment', function () {
    $this->app['env'] = 'local';

    $response = $this->get(route('login'))->assertOk();
    foreach (['admin', 'staf', 'andi', 'budi', 'asdos', 'dosen'] as $account) {
        $response->assertSee(route('login.demo', ['account' => $account]))
            ->assertSee('name="_token"', false);
    }

    $this->app['env'] = 'testing';
    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee(route('login.demo', ['account' => 'andi']));
});

it('quick logs into only the seeded demo account in local environment', function (string $account, string $email, string $role, string $homeRoute) {
    $this->app['env'] = 'local';
    $this->seed(DemoUserSeeder::class);

    $user = User::where('email', $email)->firstOrFail();
    $this->get(route('login'));
    $sessionIdBeforeLogin = session()->getId();
    $csrfToken = session()->token();

    $this->post(route('login.demo', ['account' => $account]), ['_token' => $csrfToken])
        ->assertRedirect(route($homeRoute, absolute: false));
    $this->assertAuthenticatedAs($user);

    expect($user->hasRole($role))->toBeTrue()
        ->and(session()->getId())->not->toBe($sessionIdBeforeLogin);
})->with([
    'Andi' => ['andi', 'andi@example.com', 'mahasiswa', 'dashboard'],
    'Budi' => ['budi', 'budi@example.com', 'mahasiswa', 'dashboard'],
    'Asdos' => ['asdos', 'asdos@example.com', 'asdos', 'admin.dashboard'],
    'Dosen' => ['dosen', 'dosen@example.com', 'dosen', 'admin.dashboard'],
    'Admin' => ['admin', 'admin@example.com', 'admin', 'admin.dashboard'],
    'Staf' => ['staf', 'staf@example.com', 'staf', 'admin.dashboard'],
]);

it('does not expose quick login outside local or allow arbitrary account names', function () {
    $this->app['env'] = 'testing';
    $this->get(route('login'));
    $csrfToken = session()->token();

    $this->post(route('login.demo', ['account' => 'dosen']), ['_token' => $csrfToken])
        ->assertNotFound();

    $this->app['env'] = 'local';
    $this->post(route('login.demo', ['account' => 'administrator']), ['_token' => $csrfToken])
        ->assertNotFound();
    $this->assertGuest();
});

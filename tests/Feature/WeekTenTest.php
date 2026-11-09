<?php

use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests and forbids students from the admin panel', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->get(route('admin.proposals.index'))->assertRedirect(route('login'));

    $student = User::factory()->withRole('mahasiswa')->create();

    $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($student)->get(route('admin.proposals.index'))->assertForbidden();
});

it('renders the AdminLTE dashboard with aggregate statistic widgets', function () {
    $students = User::factory()->withRole('mahasiswa')->count(3)->create();
    Proposal::factory()->for($students[0])->graded()->count(2)->create();
    Proposal::factory()->for($students[1])->ungraded()->create();
    Project::factory()->for($students[2])->count(4)->create();

    $admin = User::factory()->withRole('admin')->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('small-box', false)
        ->assertSeeInOrder(['<h3>3</h3>', 'Total Mahasiswa'], false)
        ->assertSeeInOrder(['<h3>3</h3>', 'Total Proposal'], false)
        ->assertSeeInOrder(['<h3>1</h3>', 'Proposal Belum Dinilai'], false)
        ->assertSeeInOrder(['<h3>4</h3>', 'Total Project Agentic AI'], false);
});

it('shows the delete action only to admins and enforces it on the server', function () {
    $proposal = Proposal::factory()->create(['title' => 'Proposal Uji Hapus']);

    $staf = User::factory()->withRole('staf')->create();

    $this->actingAs($staf)
        ->get(route('admin.proposals.index'))
        ->assertOk()
        ->assertSee('Proposal Uji Hapus')
        ->assertDontSee(route('admin.proposals.destroy', $proposal));

    $this->actingAs($staf)
        ->delete(route('admin.proposals.destroy', $proposal))
        ->assertForbidden();
    $this->assertModelExists($proposal);

    $admin = User::factory()->withRole('admin')->create();

    $this->actingAs($admin)
        ->get(route('admin.proposals.index'))
        ->assertSee(route('admin.proposals.destroy', $proposal));

    $this->actingAs($admin)
        ->from(route('admin.proposals.index'))
        ->delete(route('admin.proposals.destroy', $proposal))
        ->assertRedirect(route('admin.proposals.index'))
        ->assertSessionHas('status');
    $this->assertModelMissing($proposal);
});

it('limits staff to viewing proposals without grading', function () {
    $staf = User::factory()->withRole('staf')->create();

    $this->actingAs($staf)->get(route('admin.grades.index'))->assertForbidden();
    $this->actingAs($staf)
        ->patch(route('admin.grades.update', Proposal::factory()->create()), ['grade' => 90])
        ->assertForbidden();
});

it('searches proposals by title or student name and filters by grade status', function () {
    $andi = User::factory()->withRole('mahasiswa')->create(['name' => 'Andi Pencarian']);
    $budi = User::factory()->withRole('mahasiswa')->create(['name' => 'Budi Lain']);

    Proposal::factory()->for($andi)->graded()->create(['title' => 'Chatbot Akademik']);
    Proposal::factory()->for($budi)->ungraded()->create(['title' => 'Asisten Jadwal']);
    Proposal::factory()->for($budi)->graded()->create(['title' => 'Ringkasan Materi']);

    $admin = User::factory()->withRole('admin')->create();
    $this->actingAs($admin);

    $this->get(route('admin.proposals.index', ['search' => 'Chatbot']))
        ->assertSee('Chatbot Akademik')
        ->assertDontSee('Asisten Jadwal')
        ->assertDontSee('Ringkasan Materi');

    $this->get(route('admin.proposals.index', ['search' => 'Budi']))
        ->assertSee('Asisten Jadwal')
        ->assertSee('Ringkasan Materi')
        ->assertDontSee('Chatbot Akademik');

    $this->get(route('admin.proposals.index', ['search' => 'Budi', 'status' => 'belum']))
        ->assertSee('Asisten Jadwal')
        ->assertDontSee('Ringkasan Materi');

    $this->get(route('admin.proposals.index', ['status' => 'dinilai']))
        ->assertSee('Chatbot Akademik')
        ->assertSee('Ringkasan Materi')
        ->assertDontSee('Asisten Jadwal');
});

it('keeps search filters in pagination links', function () {
    $student = User::factory()->withRole('mahasiswa')->create();
    Proposal::factory()->for($student)->ungraded()->count(12)->create();

    $admin = User::factory()->withRole('admin')->create();

    $response = $this->actingAs($admin)
        ->get(route('admin.proposals.index', ['search' => 'Agent', 'status' => 'belum']))
        ->assertOk();

    $nextPage = e(route('admin.proposals.index', ['search' => 'Agent', 'status' => 'belum', 'page' => 2]));
    $response->assertSee($nextPage, false);
});

it('redirects users after login according to their Spatie role', function (string $email, string $expectedRoute) {
    $this->seed(DemoUserSeeder::class);

    $this->post(route('login'), ['email' => $email, 'password' => 'password'])
        ->assertRedirect(route($expectedRoute, absolute: false));
})->with([
    'admin' => ['admin@example.com', 'admin.dashboard'],
    'staf' => ['staf@example.com', 'admin.dashboard'],
    'mahasiswa' => ['andi@example.com', 'dashboard'],
]);

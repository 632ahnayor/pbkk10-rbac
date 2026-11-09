<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProposalController as AdminProposalController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Auth\DemoLoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProposalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])
    ->name('home');

Route::get('/local-login', [PageController::class, 'localLogin'])
    ->name('local-login');

Route::prefix('dashboard')->group(function () {
    Route::get('/about', [PageController::class, 'about'])
        ->name('about');
    Route::get('/project-idea', [PageController::class, 'project'])
        ->name('project');
});
Route::get('/hitung/{angka1}/{angka2}/{operasi}', [PageController::class, 'kalkulator'])
    ->name('kalkulator');
Route::get('/mahasiswa/{nrp}', [PageController::class, 'mahasiswa'])
    ->where('nrp', '[0-9]{10}')
    ->name('mahasiswa.show');
Route::get('/agent/{tema?}', [AgentController::class, 'agent'])
    ->name('agent');
Route::get('/hitung-ipk', [PageController::class, 'hitungipkForm'])
    ->name('hitung-ipk');
Route::get('/hitung-ipk/{ip1}/{ip2}', [PageController::class, 'hitungipkResult'])
    ->name('hitung-ipk.result');
Route::post('/hitung-ipk', [PageController::class, 'hitungipk'])
    ->name('hitung-ipk.calculate');

Route::get('/profil-mahasiswa', [PageController::class, 'profilMahasiswa'])
    ->name('profilMahasiswa');
Route::get('/ide-agent', [PageController::class, 'ideAgent'])
    ->name('ideAgent');

Route::get('/form', [PageController::class, 'form'])
    ->name('form.get');
Route::post('/form', [PageController::class, 'formPost'])
    ->name('form.post');

Route::get('/dashboard', DashboardController::class)
    ->middleware('auth')
    ->name('dashboard');

Route::post('/login/as/{account}', DemoLoginController::class)
    ->middleware('guest')
    ->name('login.demo');

Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
    Route::get('/proposals/create', [ProposalController::class, 'create'])
        ->name('proposals.create');
    Route::post('/proposals', [ProposalController::class, 'store'])
        ->middleware('throttle:proposal-submissions')
        ->name('proposals.store');
    Route::get('/proposals/{proposal}/edit', [ProposalController::class, 'edit'])
        ->name('proposals.edit');
    Route::patch('/proposals/{proposal}', [ProposalController::class, 'update'])
        ->name('proposals.update');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin|staf|dosen|asdos'])
    ->group(function () {
        Route::redirect('/', '/admin/dashboard')->name('index');
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/proposals', [AdminProposalController::class, 'index'])->name('proposals.index');
        Route::delete('/proposals/{proposal}', [AdminProposalController::class, 'destroy'])->name('proposals.destroy');
        Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
        Route::patch('/grades/{proposal}', [GradeController::class, 'update'])->name('grades.update');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::fallback(function () {
    return response()->view('fallback', ['pageTitle' => 'Not Found'], 404);
})
    ->name('fallback');

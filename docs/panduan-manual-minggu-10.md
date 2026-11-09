# Panduan Manual Minggu 10: Admin Dashboard & RBAC

Panduan ini mengulang seluruh perubahan Tugas Minggu 10 secara manual, mulai dari branch `minggu-6`. Kerjakan langkah-langkahnya berurutan. Setiap langkah berisi **tujuan**, **perintah** yang dijalankan, **edit** (blok *Sebelum* → *Sesudah*), dan **cek hasil** untuk memastikan langkah itu berhasil sebelum lanjut.

> Semua perintah ditulis untuk PowerShell di folder proyek `pbkk-evolution`.

## Daftar isi

0. [Siapkan branch](#langkah-0--siapkan-branch)
1. [Instal paket Spatie dan AdminLTE](#langkah-1--instal-paket-spatie-dan-adminlte)
2. [Pindahkan peran ke Spatie](#langkah-2--pindahkan-peran-ke-spatie)
3. [Seeder peran, izin, dan data Faker](#langkah-3--seeder-peran-izin-dan-data-faker)
4. [Konfigurasi AdminLTE](#langkah-4--konfigurasi-adminlte)
5. [Route admin](#langkah-5--route-admin)
6. [Controller admin](#langkah-6--controller-admin)
7. [View AdminLTE](#langkah-7--view-adminlte)
8. [Redirect login per peran dan quick login](#langkah-8--redirect-login-per-peran-dan-quick-login)
9. [Tes](#langkah-9--tes)
10. [README dan demo](#langkah-10--readme-dan-demo)
- [Kenapa beda dari slide?](#kenapa-beda-dari-slide)
- [Troubleshooting](#troubleshooting)

---

## Langkah 0 — Siapkan branch

**Tujuan:** pekerjaan minggu 10 terpisah dari minggu 6, sehingga mudah dibandingkan dan dikembalikan.

```powershell
git status                 # pastikan tidak ada perubahan yang tertinggal
git checkout minggu-6
git checkout -b minggu-10
```

**Cek hasil:** `git branch` menandai `* minggu-10`. Pastikan `.gitignore` masih memuat `.env` dan `/vendor`.

---

## Langkah 1 — Instal paket Spatie dan AdminLTE

**Tujuan:** memasang paket otorisasi (slide 14–15) dan template admin (slide 6–8).

```powershell
composer require spatie/laravel-permission jeroennoten/laravel-adminlte
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan adminlte:install
```

Perintah di atas menghasilkan:

| File / folder baru | Asal |
| --- | --- |
| `config/permission.php` | Spatie |
| `database/migrations/xxxx_create_permission_tables.php` | Spatie, membuat 5 tabel: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` |
| `config/adminlte.php` | AdminLTE |
| `public/vendor/adminlte/` | Aset CSS/JS AdminLTE |
| `lang/vendor/adminlte/` | Terjemahan AdminLTE |

> `adminlte:install` akan menyebut beberapa aset pihak ketiga (Bootstrap 5, Bootstrap Icons) "skipped". Itu normal: AdminLTE memakai CDN sebagai fallback.

**Cek hasil:** `composer show spatie/laravel-permission` menampilkan versi 8.x, dan `composer show jeroennoten/laravel-adminlte` menampilkan versi 4.x.

---

## Langkah 2 — Pindahkan peran ke Spatie

**Tujuan:** hak akses disimpan di tabel Spatie (spesifikasi wajib no. 3), bukan lagi di kolom `users.role`.

### 2a. Model User: trait `HasRoles`

File: `app/Models/User.php`

Sebelum:

```php
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
```

Sesudah:

```php
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Peran yang boleh membuka panel admin AdminLTE.
     *
     * @var list<string>
     */
    public const PANEL_ROLES = ['admin', 'staf', 'dosen', 'asdos'];
```

Tambahkan juga method berikut di bawah relasi `projects()`. Method ini dipakai di langkah 8.

```php
    /**
     * Nama route tujuan setelah login berdasarkan peran Spatie.
     */
    public function homeRoute(): string
    {
        return $this->hasAnyRole(self::PANEL_ROLES) ? 'admin.dashboard' : 'dashboard';
    }
```

### 2b. Migration: hapus kolom `role`

```powershell
php artisan make:migration drop_role_from_users_table
```

Isi file barunya:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Peran pengguna kini disimpan di tabel otorisasi Spatie (model_has_roles).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('mahasiswa')->after('password');
        });
    }
};
```

Opsional (slide 31, optimasi pencarian): buat `php artisan make:migration add_title_index_to_proposals_table` dengan isi berikut.

```php
public function up(): void
{
    Schema::table('proposals', function (Blueprint $table) {
        $table->index('title');
    });
}

public function down(): void
{
    Schema::table('proposals', function (Blueprint $table) {
        $table->dropIndex(['title']);
    });
}
```

### 2c. Ganti middleware `cek.peran` dengan middleware Spatie

1. Hapus file `app/Http/Middleware/CheckRole.php`.
2. Edit `bootstrap/app.php`.

Sebelum:

```php
use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
// ...
        $middleware->alias([
            'cek.peran' => CheckRole::class,
        ]);
```

Sesudah:

```php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
// ...
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
```

### 2d. Hapus Gate manual

File: `app/Providers/AppServiceProvider.php`. Hapus baris berikut, beserta `use App\Models\User;` dan `use Illuminate\Support\Facades\Gate;` yang tidak terpakai lagi:

```php
Gate::define('kelola-nilai', fn (User $user): bool => in_array($user->role, ['dosen', 'asdos'], true));
```

Gate ini tidak perlu lagi karena Spatie otomatis mendaftarkan setiap permission ke Gate Laravel. `Gate::authorize('kelola-nilai')` di `GradeController` dan `@can('kelola-nilai')` di Blade tetap berfungsi tanpa diubah.

### 2e. Policy, registrasi, dan Blade portal

`app/Policies/ProposalPolicy.php`:

```php
// Sebelum
return $user->role === 'mahasiswa' && $user->id === $proposal->user_id;
// Sesudah
return $user->hasRole('mahasiswa') && $user->id === $proposal->user_id;
```

`app/Http/Controllers/Auth/RegisteredUserController.php`:

```php
// Sebelum
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'role' => 'mahasiswa',
]);

// Sesudah
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
]);

// Peran ditetapkan server, bukan dari input form.
$user->assignRole('mahasiswa');
```

`resources/views/layouts/navigation.blade.php`: tiga blok link (Buat Proposal, Input Nilai, Tinjau Proposal) diganti menjadi dua blok berikut.

```blade
@role('mahasiswa')
    <x-nav-link :href="route('proposals.create')" :active="request()->routeIs('proposals.*')">
        {{ __('Buat Proposal') }}
    </x-nav-link>
@endrole
@hasanyrole(App\Models\User::PANEL_ROLES)
    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
        {{ __('Panel Admin') }}
    </x-nav-link>
@endhasanyrole
```

`resources/views/dashboard.blade.php`:

| Sebelum | Sesudah |
| --- | --- |
| `@if (Auth::user()->role === 'mahasiswa')` | `@role('mahasiswa')` |
| `{{ Auth::user()->role }}` | `{{ Auth::user()->getRoleNames()->join(', ') }}` |
| `@if (in_array(Auth::user()->role, ['dosen', 'asdos'], true))` … `route('admin.index')` … `@endif` | `@hasanyrole(App\Models\User::PANEL_ROLES)` … `route('admin.dashboard')` … `@endhasanyrole` |
| `@endif` penutup blok terluar | `@endrole` (`@else` di tengahnya tetap) |

**Cek hasil:** jalankan `Select-String -Path app,resources,routes,database -Pattern "->role\b|'role' =>|cek.peran" -Recurse`. Hasilnya harus kosong, kecuali migration lama `add_role_to_users_table`.

---

## Langkah 3 — Seeder peran, izin, dan data Faker

**Tujuan:** mendaftarkan peran Admin (penuh) dan Staf (terbatas) secara otomatis (slide 33, Contoh 1), lalu mengisi data dummy untuk demo (challenge A+).

### 3a. `RoleAndPermissionSeeder` (file baru)

```powershell
php artisan make:seeder RoleAndPermissionSeeder
```

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Matriks peran => izin. Admin memegang seluruh izin.
     *
     * @var array<string, list<string>>
     */
    public const MATRIX = [
        'admin' => ['lihat-proposal', 'kelola-nilai', 'hapus-proposal'],
        'staf' => ['lihat-proposal'],
        'dosen' => ['lihat-proposal', 'kelola-nilai'],
        'asdos' => ['lihat-proposal', 'kelola-nilai'],
        'mahasiswa' => ['ajukan-proposal'],
    ];

    public function run(): void
    {
        // Spatie meng-cache izin; kosongkan agar perubahan langsung berlaku.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (collect(self::MATRIX)->flatten()->unique() as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        foreach (self::MATRIX as $role => $permissions) {
            Role::firstOrCreate(['name' => $role])->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
```

> `firstOrCreate` + `syncPermissions` membuat seeder aman dijalankan berkali-kali (idempotent).

### 3b. `DemoUserSeeder`: tambah Admin dan Staf

```php
foreach ([
    ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'admin'],
    ['name' => 'Staf', 'email' => 'staf@example.com', 'role' => 'staf'],
    ['name' => 'Andi', 'email' => 'andi@example.com', 'role' => 'mahasiswa'],
    ['name' => 'Budi', 'email' => 'budi@example.com', 'role' => 'mahasiswa'],
    ['name' => 'Asdos', 'email' => 'asdos@example.com', 'role' => 'asdos'],
    ['name' => 'Dosen', 'email' => 'dosen@example.com', 'role' => 'dosen'],
] as $demoUser) {
    User::updateOrCreate(
        ['email' => $demoUser['email']],
        ['name' => $demoUser['name'], 'password' => 'password', 'email_verified_at' => now()],
    )->syncRoles([$demoUser['role']]);
}
```

Perbedaannya dengan versi lama: `role` tidak lagi ikut disimpan ke tabel `users`, melainkan diberikan lewat `syncRoles()`.

### 3c. `ProposalFactory` (file baru) + `HasFactory` di model Proposal

```powershell
php artisan make:factory ProposalFactory --model=Proposal
```

```php
public function definition(): array
{
    return [
        'user_id' => User::factory(),
        'title' => 'Agent '.fake()->words(3, true),
        'description' => fake()->paragraph(),
        'grade' => fake()->boolean() ? fake()->randomFloat(2, 60, 100) : null,
    ];
}

public function ungraded(): static
{
    return $this->state(fn (array $attributes) => ['grade' => null]);
}

public function graded(float $grade = 85): static
{
    return $this->state(fn (array $attributes) => ['grade' => $grade]);
}
```

Di `app/Models/Proposal.php`, tambahkan `use HasFactory;` di dalam class beserta import-nya:

```php
use Database\Factories\ProposalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// ...
class Proposal extends Model
{
    /** @use HasFactory<ProposalFactory> */
    use HasFactory;
```

### 3d. `UserFactory`: hapus `role`, tambah state `withRole()`

Hapus baris `'role' => 'mahasiswa',` dari `definition()`, lalu tambahkan method berikut.

```php
/**
 * Memberikan peran Spatie setelah user dibuat.
 */
public function withRole(string $role): static
{
    return $this->afterCreating(fn (User $user) => $user->assignRole($role));
}
```

Pemakaian: `User::factory()->withRole('admin')->create()`.

### 3e. `DatabaseSeeder`

1. Panggil seeder peran **paling awal**:

   ```php
   $this->call([
       RoleAndPermissionSeeder::class,
       DemoUserSeeder::class,
   ]);
   ```

2. Pada setiap `User::firstOrCreate(...)` (Wira, 15 mahasiswa week6, Dosen PBKK), hapus `'role' => ...` dari array, lalu tambahkan baris peran setelahnya, misalnya `$wira->syncRoles(['mahasiswa']);`, `$student->syncRoles(['mahasiswa']);`, dan `$demoLecturer->syncRoles(['dosen']);`.

3. Di dalam loop 15 mahasiswa, setelah blok project, tambahkan:

   ```php
   // Minggu 10: proposal dummy Faker untuk demo pencarian, filter, dan pagination admin.
   $proposalsToCreate = max(0, 2 - $student->proposals()->count());

   if ($proposalsToCreate > 0) {
       $student->proposals()->saveMany(
           Proposal::factory()->count($proposalsToCreate)->make(['user_id' => $student->id])
       );
   }
   ```

   Tambahkan `use App\Models\Proposal;` di bagian atas file.

   > `make(['user_id' => $student->id])` mencegah factory membuat user baru dari `'user_id' => User::factory()`.

**Cek hasil:**

```powershell
php artisan migrate:fresh --seed
php artisan permission:show
```

`permission:show` menampilkan tabel izin × peran yang sama dengan matriks di atas. Di Tinker, `App\Models\Proposal::count()` harus bernilai 30.

---

## Langkah 4 — Konfigurasi AdminLTE

**Tujuan:** sidebar dinamis yang mengikuti izin user (slide 8).

File: `config/adminlte.php`

| Kunci | Sebelum | Sesudah |
| --- | --- | --- |
| `title` | `'AdminLTE 4'` | `'PBKK Evolution Admin'` |
| `title_postfix` | `''` | `' \| PBKK Evolution Admin'` |
| `logo` | `'<b>Admin</b>LTE'` | `'<b>PBKK</b> Evolution'` |
| `use_route_url` | `false` | `true` (URL di bawah berupa nama route) |
| `dashboard_url` | `'home'` | `'admin.dashboard'` |
| `password_reset_url` | `'password/reset'` | `'password.request'` |
| `password_email_url` | `'password/email'` | `'password.email'` |
| `profile_url` | `false` | `'profile.edit'` |

Pada array `'menu'`:

- hapus item `navbar-search` (tidak punya tujuan pencarian);
- hapus semua item contoh sidebar (`blog`, `pages`, `multilevel`, `labels`, dan seterusnya), lalu ganti dengan:

```php
// Sidebar items (filter 'can' membaca permission Spatie lewat Gate):
[
    'text' => 'Dashboard',
    'route' => 'admin.dashboard',
    'icon' => 'bi bi-speedometer2',
],
['header' => 'AKADEMIK'],
[
    'text' => 'Proposal',
    'route' => 'admin.proposals.index',
    'icon' => 'bi bi-file-earmark-text',
    'can' => 'lihat-proposal',
],
[
    'text' => 'Input Nilai',
    'route' => 'admin.grades.index',
    'icon' => 'bi bi-pencil-square',
    'can' => 'kelola-nilai',
],
['header' => 'AKUN'],
[
    'text' => 'Profil',
    'route' => 'profile.edit',
    'icon' => 'bi bi-person',
],
[
    'text' => 'Kembali ke Portal',
    'route' => 'home',
    'icon' => 'bi bi-house',
],
```

> AdminLTE 4 memakai **Bootstrap Icons** (`bi bi-...`), bukan Font Awesome (`fas fa-...`) seperti AdminLTE 3.

**Cek hasil:** setelah langkah 5–7 selesai, login sebagai staf. Menu "Input Nilai" tidak muncul di sidebar.

---

## Langkah 5 — Route admin

**Tujuan:** mengunci seluruh panel admin dengan `auth` + middleware peran Spatie (slide 19, 23; Contoh 5).

File: `routes/web.php`

Import (ganti `use App\Http\Controllers\AdminController;`):

```php
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProposalController as AdminProposalController;
```

Grup mahasiswa:

```php
// Sebelum
Route::middleware(['auth', 'cek.peran:mahasiswa'])->group(function () {
// Sesudah
Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
```

Grup admin, sebelum:

```php
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'cek.peran:dosen,asdos'])
    ->group(function () {
        Route::get('/', AdminController::class)->name('index');
        Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
        Route::patch('/grades/{proposal}', [GradeController::class, 'update'])->name('grades.update');
    });
```

Grup admin, sesudah:

```php
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
```

> Pada Spatie, operator OR ditulis dengan `|` (`role:admin|staf`), bukan koma seperti `cek.peran:dosen,asdos`.

Hapus `app/Http/Controllers/AdminController.php` dan `resources/views/admin/index.blade.php`, karena keduanya digantikan langkah 6–7.

**Cek hasil:** `php artisan route:list --path=admin` menampilkan 6 route `admin.*`.

---

## Langkah 6 — Controller admin

**Tujuan:** widget statistik (slide 24), pencarian `when()` + `withQueryString()` (slide 28–29, Contoh 3–4), dan pengaman server-side untuk aksi hapus (slide 25, Contoh 2).

```powershell
php artisan make:controller Admin/DashboardController --invokable
php artisan make:controller Admin/ProposalController
```

### `app/Http/Controllers/Admin/DashboardController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalMahasiswa' => User::role('mahasiswa')->count(),
            'totalProposal' => Proposal::count(),
            'proposalBelumDinilai' => Proposal::whereNull('grade')->count(),
            'totalProject' => Project::count(),
        ]);
    }
}
```

> `User::role('mahasiswa')` adalah *query scope* dari trait `HasRoles`.

### `app/Http/Controllers/Admin/ProposalController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ProposalController extends Controller implements HasMiddleware
{
    /**
     * Pengganti `$this->middleware()` di constructor pada Laravel 11+.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:lihat-proposal', only: ['index']),
            new Middleware('permission:hapus-proposal', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $status = $request->input('status');

        $proposals = Proposal::query()
            ->with('user')
            ->when($request->input('search'), function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status === 'dinilai', fn ($query) => $query->whereNotNull('grade'))
            ->when($status === 'belum', fn ($query) => $query->whereNull('grade'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.proposals.index', [
            'proposals' => $proposals,
        ]);
    }

    public function destroy(Proposal $proposal): RedirectResponse
    {
        $proposal->delete();

        return redirect()->back()->with('status', "Proposal \"{$proposal->title}\" berhasil dihapus.");
    }
}
```

Hal penting pada kode di atas:

- `where(function ...)` membungkus `title LIKE ... OR user.name LIKE ...` dalam tanda kurung, sehingga filter status yang ditambahkan setelahnya tidak "bocor" ke bagian OR.
- `with('user')` mencegah N+1 query saat tabel menampilkan nama mahasiswa (slide 31).
- `withQueryString()` menjaga `search` dan `status` di link halaman berikutnya.

**Cek hasil:** `php artisan route:list --path=admin -v` menampilkan middleware `permission:hapus-proposal` pada route `DELETE`.

---

## Langkah 7 — View AdminLTE

**Tujuan:** semua halaman admin mewarisi `@extends('adminlte::page')` (spesifikasi wajib no. 1), dengan minimal 3 widget (spesifikasi no. 4).

### `resources/views/admin/dashboard.blade.php` (baru)

```blade
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard Admin</h1>
@endsection

@section('content')
    <p class="text-body-secondary">
        Selamat datang, {{ Auth::user()->name }}
        <span class="badge text-bg-secondary">{{ Auth::user()->getRoleNames()->join(', ') }}</span>
    </p>

    <div class="row">
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box :title="$totalMahasiswa" text="Total Mahasiswa" icon="bi bi-people-fill" theme="primary" />
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box :title="$totalProposal" text="Total Proposal" icon="bi bi-file-earmark-text-fill" theme="success"
                :url="Auth::user()->can('lihat-proposal') ? route('admin.proposals.index') : null" url-text="Lihat proposal" />
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box :title="$proposalBelumDinilai" text="Proposal Belum Dinilai" icon="bi bi-hourglass-split" theme="warning"
                :url="Auth::user()->can('lihat-proposal') ? route('admin.proposals.index', ['status' => 'belum']) : null" url-text="Lihat daftar" />
        </div>
        <div class="col-lg-3 col-6">
            <x-adminlte-small-box :title="$totalProject" text="Total Project Agentic AI" icon="bi bi-robot" theme="danger" />
        </div>
    </div>

    @role('staf')
        <div class="alert alert-info">
            Akun staf memiliki akses terbatas: dapat melihat proposal, tetapi tidak dapat menilai atau menghapus data.
        </div>
    @endrole
@endsection
```

### `resources/views/admin/proposals/index.blade.php` (baru)

Bagian-bagian kuncinya (lihat file lengkapnya di repo):

```blade
@extends('adminlte::page')

{{-- 1. Form filter GET: nilai lama tetap terisi lewat request() --}}
<form method="GET" action="{{ route('admin.proposals.index') }}" class="row g-2 align-items-end" role="search">
    <input id="search" name="search" type="search" value="{{ request('search') }}" class="form-control">
    <select id="status" name="status" class="form-select">
        <option value="">Semua</option>
        <option value="dinilai" @selected(request('status') === 'dinilai')>Sudah dinilai</option>
        <option value="belum" @selected(request('status') === 'belum')>Belum dinilai</option>
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
</form>

{{-- 2. Tombol Hapus hanya dirender untuk pemegang izin hapus-proposal --}}
@can('hapus-proposal')
    <form method="POST" action="{{ route('admin.proposals.destroy', $proposal) }}" class="d-inline"
        onsubmit="return confirm('Hapus proposal ini?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
    </form>
@endcan

{{-- 3. Pagination memakai template Bootstrap 5 (AdminLTE 4) --}}
{{ $proposals->links('pagination::bootstrap-5') }}
```

> `links('pagination::bootstrap-5')` dipasang hanya di view ini. `Paginator::useBootstrapFive()` sengaja tidak dipakai karena akan mengubah pagination di halaman Breeze/Tailwind juga.

### `resources/views/admin/grades.blade.php` (diubah)

Ganti pembungkus `<x-app-layout>` dengan `@extends('adminlte::page')` + `@section('content')`. Ganti juga kelas Tailwind dengan kelas Bootstrap (`card`, `list-group`, `form-control`, `btn btn-primary`). Logika `@can('kelola-nilai')` tetap sama.

**Cek hasil:** `composer run dev`, lalu buka `/admin/dashboard` sebagai admin. Sidebar, navbar, dan 4 kotak widget berwarna harus tampil.

---

## Langkah 8 — Redirect login per peran dan quick login

**Tujuan:** setelah login, admin otomatis masuk ke panel khusus (slide 22–23); tombol login instan memudahkan demo (challenge A+).

`app/Http/Controllers/Auth/AuthenticatedSessionController.php` → method `store()`:

```php
// Sebelum
return redirect()->intended(route('dashboard', absolute: false));

// Sesudah
// Admin/staf/dosen/asdos diarahkan ke panel AdminLTE, mahasiswa ke dashboard portal.
return redirect()->intended(route($request->user()->homeRoute(), absolute: false));
```

`app/Http/Controllers/Auth/DemoLoginController.php`:

```php
$demoAccounts = [
    'admin' => 'admin@example.com',   // baru
    'staf' => 'staf@example.com',     // baru
    'andi' => 'andi@example.com',
    // ...
];
// ...
return redirect()->intended(route($user->homeRoute(), absolute: false));
```

`resources/views/auth/login.blade.php`: tambahkan dua entri di awal array `@foreach`:

```blade
@foreach (['admin' => 'Admin (Akses Penuh)', 'staf' => 'Staf (Terbatas)', 'andi' => 'Andi (Mahasiswa)', /* ... */] as $account => $label)
```

**Cek hasil:** di halaman `/login` (APP_ENV=local), klik **Admin (Akses Penuh)**. Anda akan diarahkan ke `/admin/dashboard`. Klik **Andi**, maka Anda diarahkan ke `/dashboard`.

---

## Langkah 9 — Tes

**Tujuan:** membuktikan penguncian bekerja di server, bukan hanya tersembunyi di UI.

### 9a. Seed peran otomatis untuk setiap tes database

`tests/TestCase.php`:

```php
use Database\Seeders\RoleAndPermissionSeeder;

abstract class TestCase extends BaseTestCase
{
    /**
     * Tes yang memakai RefreshDatabase selalu memiliki peran & izin Spatie.
     */
    protected $seed = true;

    protected $seeder = RoleAndPermissionSeeder::class;
}
```

### 9b. Perbarui tes lama

| Sebelum | Sesudah |
| --- | --- |
| `User::factory()->create(['role' => 'dosen'])` | `User::factory()->withRole('dosen')->create()` |
| `expect($user->role)->toBe('dosen')` | `expect($user->hasRole('dosen'))->toBeTrue()` |
| `assertDatabaseHas('users', ['role' => ...])` | `expect($user->getRoleNames()->all())->toBe([...])` |
| `route('admin.index')` | `route('admin.dashboard')` |
| redirect login dosen/asdos ke `dashboard` | redirect ke `admin.dashboard` |

### 9c. Tes baru `tests/Feature/WeekTenTest.php`

```powershell
php artisan make:test WeekTenTest --pest
```

Skenario yang diuji (lihat file lengkapnya di repo):

1. Guest diarahkan ke login; mahasiswa mendapat 403 di `/admin/*`.
2. Angka widget dashboard sesuai isi database.
3. Staf tidak melihat tombol Hapus dan `DELETE` oleh staf mendapat 403 (data tetap ada); admin berhasil menghapus.
4. Staf mendapat 403 di halaman/aksi nilai.
5. Pencarian judul **atau** nama mahasiswa, plus filter status.
6. Link halaman 2 masih membawa `search` dan `status`.
7. Redirect login per peran.

**Cek hasil:**

```powershell
php artisan test
vendor\bin\pint --dirty --test
```

> `FormTest > it accepts valid form data` memang sudah gagal sejak sebelum Minggu 10 (validasi email `@student.its.ac.id` di form Minggu 4), jadi tidak terkait perubahan ini.

---

## Langkah 10 — README dan demo

1. Tambahkan bagian **Tugas 10** di README berisi instalasi, tabel akun uji, matriks peran, dan screenshot (ketentuan pengumpulan slide 43).
2. Ambil screenshot `/admin/dashboard` dan simpan sebagai `docs/screenshots/admin-dashboard.png`.
3. Latih demo maksimal 3 menit:
   - Admin: lihat widget, cari/filter, pindah halaman, lalu hapus.
   - Staf: tombol Hapus tidak ada, dan `/admin/grades` mendapat 403.
   - Andi: `/admin/dashboard` mendapat 403.
4. Commit dan push branch, lalu unggah link repo ke LMS paling lambat H-1 Pertemuan 11.

---

## Kenapa beda dari slide?

Slide ditulis dengan gaya Laravel 10 ke bawah. Proyek ini memakai **Laravel 13**, jadi ada tiga penyesuaian:

| Slide | Masalah di Laravel 11+ | Yang dipakai di proyek |
| --- | --- | --- |
| `$this->middleware('permission:hapus-tugas')->only('destroy');` di `__construct()` (Contoh 2) | Base `Controller` kini kosong, sehingga method `middleware()` tidak ada dan muncul error *Call to undefined method* | `implements HasMiddleware` + `public static function middleware(): array { return [new Middleware('permission:hapus-proposal', only: ['destroy'])]; }` |
| `$this->authorize('hapus-tugas', $tugas);` (slide 25) | Trait `AuthorizesRequests` tidak lagi dipasang di base `Controller` | `Gate::authorize('kelola-nilai');` |
| Middleware `role:` / `permission:` langsung dipakai di route | Laravel 11+ tidak punya `Kernel.php`, dan Spatie v8 tidak mendaftarkan alias otomatis | Alias didaftarkan di `bootstrap/app.php` → `$middleware->alias([...])` |

Selain itu, AdminLTE versi paket terbaru (v4) memakai **Bootstrap 5 + Bootstrap Icons**, sedangkan slide menyebut Bootstrap 4 + jQuery (AdminLTE 3). Cara pakainya tetap sama: `@extends('adminlte::page')`, `@section('content')`, dan menu di `config/adminlte.php`.

---

## Troubleshooting

| Gejala | Penyebab | Solusi |
| --- | --- | --- |
| `There is no role named 'admin' for guard 'web'` | Seeder peran belum dijalankan, atau cache izin masih lama | `php artisan db:seed --class=RoleAndPermissionSeeder` lalu `php artisan permission:cache-reset` |
| `Target class [role] does not exist` | Alias middleware Spatie belum didaftarkan | Cek `bootstrap/app.php` (langkah 2c), lalu `php artisan optimize:clear` |
| Halaman admin tampil tanpa CSS | Aset AdminLTE belum dipublikasikan | `php artisan adminlte:install --only=assets` |
| Ikon tidak muncul | Masih memakai kelas Font Awesome (`fas fa-...`) | Ganti dengan Bootstrap Icons (`bi bi-...`) |
| 403 padahal seharusnya boleh | Peran user tidak sesuai | `php artisan tinker` → `App\Models\User::firstWhere('email', 'staf@example.com')->getRoleNames()` |
| `Column not found: role` | Masih ada kode yang memakai kolom lama | Cari `->role` atau `'role' =>` (lihat cek hasil langkah 2) |
| Menu sidebar tidak berubah setelah izin diubah | Cache konfigurasi atau izin | `php artisan optimize:clear` dan `php artisan permission:cache-reset` |

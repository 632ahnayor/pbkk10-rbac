<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Nama route tujuan setelah login berdasarkan peran Spatie.
     */
    public function homeRoute(): string
    {
        return $this->hasAnyRole(self::PANEL_ROLES) ? 'admin.dashboard' : 'dashboard';
    }
}

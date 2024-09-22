<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function doneProjects(): HasMany
    {
        return $this->hasMany(Project::class)->whereNotNull('finished_at')->whereIsMismatched(false);
    }

    public function mismatchedProjects(): HasMany
    {
        return $this->hasMany(Project::class)->whereNotNull('finished_at')->whereIsMismatched(true);
    }

    public function doingProjects(): HasMany
    {
        return $this->hasMany(Project::class)->whereFinishedAt(null);
    }

    public function isSuperAdmin(): bool
    {
        return $this->username === 'super-admin' || $this->hasRole('super_admin');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');

    }
}

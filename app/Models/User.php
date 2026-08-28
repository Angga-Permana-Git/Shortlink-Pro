<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Support\Enums\Role;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'auth_provider',
        'ldap_dn',
        'sso_subject',
        'keycloak_sub',
        'role',
        'is_protected',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'is_active' => 'boolean',
            'is_protected' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function shortUrls(): HasMany
    {
        return $this->hasMany(ShortUrl::class, 'owner_id');
    }

    public function roleChangeLogs(): HasMany
    {
        return $this->hasMany(RoleChangeLog::class, 'target_user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin->value;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isLocal(): bool
    {
        return $this->auth_provider === 'local';
    }

    public function isProtected(): bool
    {
        return (bool) $this->is_protected;
    }

    public function canLogin(): bool
    {
        return $this->isActive() && $this->password !== null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($panel->getId() === 'admin') {
            return $this->isAdmin();
        }

        return true;
    }
}
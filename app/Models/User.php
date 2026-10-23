<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use App\Support\Enums\Role;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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
        'google_sub',
        'role',
        'locale',
        'timezone',
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
        'two_factor_secret',
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
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function shortUrls(): HasMany
    {
        return $this->hasMany(ShortUrl::class, 'owner_id');
    }

    /** Seluruh klik pada semua tautan milik pengguna. */
    public function clicks(): HasManyThrough
    {
        return $this->hasManyThrough(ClickEvent::class, ShortUrl::class, 'owner_id', 'short_url_id');
    }

    public function roleChangeLogs(): HasMany
    {
        return $this->hasMany(RoleChangeLog::class, 'target_user_id');
    }

    public function linktreeProfile(): HasOne
    {
        return $this->hasOne(LinktreeProfile::class);
    }

    public function loginAuditLogs(): HasMany
    {
        return $this->hasMany(LoginAuditLog::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'actor');
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

    public function hasTwoFactor(): bool
    {
        return $this->two_factor_confirmed_at !== null
            && (string) $this->two_factor_secret !== '';
    }

    /**
     * Notifikasi bawaan Laravel diganti versi berbahasa Indonesia, sedangkan
     * token dan masa berlakunya tetap dari broker kata sandi Laravel.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
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

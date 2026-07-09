<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasMedia;
use App\Traits\HasTranslations;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'username',
        'email',
        'phone_no',
        'password',
        'email_verified_at',
        'is_verified',
        'last_login',
        'avatar',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'locale',
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

    protected $appends = [
        'full_name',
        'avatar_url',
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
            'is_verified' => 'boolean',
            'last_login' => 'datetime',
        ];
    }

    /**
     * Get the properties for the user.
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Get the favorites for the user.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the inquiries made by the user.
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    /**
     * Get the roles assigned to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    /**
     * Get the user's primary/first role.
     */
    public function role()
    {
        return $this->roles()->first();
    }

    /**
     * Get the permissions directly assigned to the user.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user')->withTimestamps();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('title', $role)->exists();
    }

    /**
     * Check if user has a specific permission (direct or through roles).
     */
    public function hasPermission(string $permission): bool
    {
        // Check direct permissions
        if ($this->permissions()->where('title', $permission)->exists()) {
            return true;
        }

        // Check permissions through roles
        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('title', $permission);
        })->exists();
    }

    /**
     * Get all permissions for the user (direct + through roles).
     */
    public function allPermissions()
    {
        $directPermissions = $this->permissions;
        $rolePermissions = $this->roles()->with('permissions')->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');

        return $directPermissions->merge($rolePermissions)->unique('id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function setNameAttribute($value): void
    {
        $parts = explode(' ', trim($value), 2);
        $this->first_name = $parts[0] ?? '';
        $this->last_name = $parts[1] ?? '';
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? url('/storage/' . ltrim($this->avatar, '/')) : null;
    }
}

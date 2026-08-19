<?php

namespace App\Models;

use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Wave\Traits\HasProfileKeyValues;
use Wave\User as WaveUser;

class User extends WaveUser
{
    use HasFactory, HasProfileKeyValues, Notifiable, SoftDeletes;

    public $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'avatar',
        'password',
        'role_id',
        'verification_code',
        'verified',
        'trial_ends_at',
        'provider_id',
    ];

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'notification_preferences' => 'array',
            'social_links' => 'array',
            'privacy_settings' => 'array',
            'deletion_scheduled_at' => 'datetime',
        ];
    }

    public function getFilamentName(): string
    {
        return $this->name ?? $this->username ?? $this->email;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin'    => $this->hasRole('admin'),
            'provider' => $this->hasRole('provider'),
            default    => false,
        };
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function kycVerification(): HasOne
    {
        return $this->hasOne(\Nucleus\Kyc\Models\KycVerification::class);
    }

    public function onboardingSubmission(): HasOne
    {
        return $this->hasOne(\Nucleus\Onboarding\Models\OnboardingSubmission::class);
    }

    public function providerProfile(): HasOne
    {
        return $this->hasOne(\Nucleus\Providers\Models\ProviderProfile::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(self::class, 'provider_id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(self::class, 'provider_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Listen for the creating event of the model
        static::creating(function ($user) {
            // Check if the username attribute is empty
            if (empty($user->username)) {
                // Use the name to generate a slugified username
                $username = Str::slug($user->name, '');
                $i = 1;
                while (self::where('username', $username)->exists()) {
                    $username = Str::slug($user->name, '').$i;
                    $i++;
                }
                $user->username = $username;
            }
        });

        // Listen for the created event of the model
        static::created(function ($user) {
            // Remove all roles
            $user->syncRoles([]);

            // Assign the default role if it exists
            $defaultRole = config('wave.default_user_role', 'registered');
            if (\Spatie\Permission\Models\Role::where('name', $defaultRole)->where('guard_name', 'web')->exists()) {
                $user->assignRole($defaultRole);
            }
        });
    }
}

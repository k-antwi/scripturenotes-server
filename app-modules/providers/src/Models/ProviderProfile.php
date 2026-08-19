<?php

namespace Nucleus\Providers\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nucleus\Providers\Database\Factories\ProviderProfileFactory;

class ProviderProfile extends Model
{
    use HasFactory;

    protected static function newFactory(): ProviderProfileFactory
    {
        return ProviderProfileFactory::new();
    }

    public const STATUS_PENDING       = 'pending';
    public const STATUS_VERIFIED      = 'verified';
    public const STATUS_FAILED        = 'failed';
    public const STATUS_MANUAL_REVIEW = 'manual_review';

    protected $fillable = [
        'user_id',
        'provider_type',
        'industry',
        'credential_reference',
        'verification_status',
        'verification_message',
        'verified_at',
        'bio',
        'is_active',
        'is_primary',
        'metadata',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'is_active'   => 'boolean',
        'is_primary'  => 'boolean',
        'metadata'    => 'array',
    ];

    protected $attributes = [
        'verification_status' => self::STATUS_PENDING,
        'is_active'           => true,
        'is_primary'          => false,
        'metadata'            => '{}',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === self::STATUS_VERIFIED;
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING       => 'Pending',
            self::STATUS_VERIFIED      => 'Verified',
            self::STATUS_FAILED        => 'Failed',
            self::STATUS_MANUAL_REVIEW => 'Manual review',
        ];
    }
}

<?php

namespace Nucleus\Organisations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organisation extends Model
{
    protected $fillable = [
        'name',
        'legal_name',
        'registration_number',
        'tax_reference',
        'organisation_type',
        'status',
        'industry_code',
        'employee_count',
        'parent_organisation_id',
        'primary_contact_name',
        'primary_contact_email',
        'primary_contact_phone',
        'address_line_1',
        'address_line_2',
        'city',
        'county',
        'postcode',
        'country_code',
        'metadata',
    ];

    protected $casts = [
        'employee_count' => 'integer',
        'metadata'       => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'parent_organisation_id');
    }

    public function subsidiaries(): HasMany
    {
        return $this->hasMany(Organisation::class, 'parent_organisation_id');
    }

    public static function typeOptions(): array
    {
        return [
            'employer' => 'Employer',
            'provider' => 'Provider',
            'partner'  => 'Partner',
            'other'    => 'Other',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'active'    => 'Active',
            'suspended' => 'Suspended',
            'dissolved' => 'Dissolved',
        ];
    }
}

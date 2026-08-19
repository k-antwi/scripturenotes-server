<?php

namespace Nucleus\Onboarding\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'current_step',
        'status',
        'first_name',
        'last_name',
        'previous_name',
        'gender',
        'dob_day',
        'dob_month',
        'dob_year',
        'mobile',
        'national_id',
        'address_postcode',
        'address_house_number',
        'address_line_1',
        'address_line_2',
        'address_city',
        'proof_of_address_path',
        'signature_data',
        'document_approval_content',
        'document_approval_status',
        'document_approval_approved_at',
        'metadata',
        'submitted_at',
    ];

    protected $casts = [
        'metadata'                      => 'array',
        'submitted_at'                  => 'datetime',
        'document_approval_approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }
}

<?php

namespace Nucleus\Kyc\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycVerification extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_reference',
        'provider_metadata',
        'status',
        'failure_reason',
        'opt_in_notification',
        'email_verified_at',
        'identity_verified_at',
        'failed_at',
        'opt_in_submitted_at',
        'proof_of_address_path',
        'proof_of_address_uploaded_at',
        'proof_of_address_result',
        'proof_of_address_note',
        'photo_id_path',
        'photo_id_uploaded_at',
        'photo_id_result',
        'photo_id_note',
        'name_change_path',
        'name_change_uploaded_at',
        'name_change_result',
        'name_change_note',
        'liveness_video_path',
        'liveness_completed_at',
        'liveness_result',
        'liveness_note',
        'reviewed_by',
        'overall_review_note',
        'submitted_at',
        'review_started_at',
        'review_completed_at',
    ];

    protected $casts = [
        'provider_metadata'            => 'array',
        'opt_in_notification'          => 'boolean',
        'email_verified_at'            => 'datetime',
        'identity_verified_at'         => 'datetime',
        'failed_at'                    => 'datetime',
        'opt_in_submitted_at'          => 'datetime',
        'proof_of_address_uploaded_at' => 'datetime',
        'photo_id_uploaded_at'         => 'datetime',
        'name_change_uploaded_at'      => 'datetime',
        'liveness_completed_at'        => 'datetime',
        'submitted_at'                 => 'datetime',
        'review_started_at'            => 'datetime',
        'review_completed_at'          => 'datetime',
    ];

    /**
     * Document types that require a physical file upload by the user.
     * Keyed by the type slug used throughout the module.
     */
    public const DOCUMENT_TYPES = [
        'proof_of_address' => [
            'label'      => 'Proof of address',
            'path_col'   => 'proof_of_address_path',
            'date_col'   => 'proof_of_address_uploaded_at',
            'result_col' => 'proof_of_address_result',
            'note_col'   => 'proof_of_address_note',
            'route'      => '/kyc/proof-of-address',
        ],
        'photo_id' => [
            'label'      => 'Proof of identity',
            'path_col'   => 'photo_id_path',
            'date_col'   => 'photo_id_uploaded_at',
            'result_col' => 'photo_id_result',
            'note_col'   => 'photo_id_note',
            'route'      => '/kyc/proof-of-identity',
        ],
        'name_change' => [
            'label'      => 'Proof of name change',
            'path_col'   => 'name_change_path',
            'date_col'   => 'name_change_uploaded_at',
            'result_col' => 'name_change_result',
            'note_col'   => 'name_change_note',
            'route'      => '/kyc/name-change',
        ],
    ];

    /**
     * All four check types (documents + liveness) used by the reviewer workspace.
     * name_change is optional — only included when a document was uploaded.
     */
    public const CHECK_TYPES = [
        'proof_of_address' => [
            'label'      => 'Proof of address',
            'result_col' => 'proof_of_address_result',
            'note_col'   => 'proof_of_address_note',
            'route'      => '/kyc/proof-of-address',
            'optional'   => false,
        ],
        'photo_id' => [
            'label'      => 'Proof of identity',
            'result_col' => 'photo_id_result',
            'note_col'   => 'photo_id_note',
            'route'      => '/kyc/proof-of-identity',
            'optional'   => false,
        ],
        'name_change' => [
            'label'      => 'Proof of name change',
            'result_col' => 'name_change_result',
            'note_col'   => 'name_change_note',
            'route'      => '/kyc/name-change',
            'optional'   => true,
        ],
        'liveness' => [
            'label'      => 'Liveness check',
            'result_col' => 'liveness_result',
            'note_col'   => 'liveness_note',
            'route'      => '/kyc/liveness',
            'optional'   => false,
        ],
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ─── Provider helpers ─────────────────────────────────────────────────────

    public function usesExternalDocumentCollection(): bool
    {
        return ! in_array($this->provider ?? 'manual', ['manual', '']);
    }

    public function usesExternalLiveness(): bool
    {
        return ! in_array($this->provider ?? 'manual', ['manual', '']);
    }

    // ─── Status checks ────────────────────────────────────────────────────────

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return ! $this->isVerified() && ! $this->isFailed();
    }

    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    public function needsMoreInfo(): bool
    {
        return $this->status === 'more_info_needed';
    }

    // ─── Document helpers ─────────────────────────────────────────────────────

    public function hasDocument(string $type): bool
    {
        $config = self::DOCUMENT_TYPES[$type] ?? null;

        return $config && ! empty($this->{$config['path_col']});
    }

    public function pendingDocumentTypes(): array
    {
        return array_values(array_filter(
            array_keys(self::DOCUMENT_TYPES),
            fn(string $type) => ! $this->hasDocument($type)
        ));
    }

    public function hasAllDocuments(): bool
    {
        return empty($this->pendingDocumentTypes());
    }

    public function hasLivenessCheck(): bool
    {
        return ! empty($this->liveness_video_path);
    }

    public function needsLivenessCheck(): bool
    {
        return $this->hasAllDocuments() && ! $this->hasLivenessCheck();
    }

    public function isReadyForVerification(): bool
    {
        return $this->hasAllDocuments() && $this->hasLivenessCheck();
    }

    /**
     * Whether the user may submit this verification for review.
     * Allows (re)submission after more_info_needed if documents are ready again.
     */
    public function canBeSubmitted(): bool
    {
        return $this->isReadyForVerification()
            && in_array($this->status, ['pending', 'documents_ready', 'more_info_needed']);
    }

    // ─── Check-result helpers ─────────────────────────────────────────────────

    /**
     * Whether all applicable check results have been decided by a reviewer.
     */
    public function allChecksDecided(): bool
    {
        foreach (self::CHECK_TYPES as $type => $config) {
            if ($config['optional'] && ! $this->hasDocument($type)) {
                continue;
            }

            if ($this->{$config['result_col']} === 'pending') {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns an array of check types that need the user's attention (result = more_info),
     * each with the reviewer's note and the re-upload route.
     *
     * @return array<string, array{label: string, note: string|null, route: string}>
     */
    public function checksNeedingAttention(): array
    {
        $result = [];

        foreach (self::CHECK_TYPES as $type => $config) {
            if ($config['optional'] && ! $this->hasDocument($type)) {
                continue;
            }

            if ($this->{$config['result_col']} === 'more_info') {
                $result[$type] = [
                    'label' => $config['label'],
                    'note'  => $this->{$config['note_col']},
                    'route' => $config['route'],
                ];
            }
        }

        return $result;
    }

    /**
     * Inspect all applicable per-check results and derive the appropriate overall status.
     *
     * Logic (in priority order):
     *   any failed          → 'failed'
     *   any more_info       → 'more_info_needed'
     *   all passed          → 'verified'
     *   otherwise           → 'under_review'
     */
    public function deriveOverallResult(): string
    {
        $results = [];

        foreach (self::CHECK_TYPES as $type => $config) {
            if ($config['optional'] && ! $this->hasDocument($type)) {
                continue;
            }

            $results[] = $this->{$config['result_col']};
        }

        if (in_array('failed', $results)) {
            return 'failed';
        }

        if (in_array('more_info', $results)) {
            return 'more_info_needed';
        }

        $allPassed = ! empty($results)
            && count(array_filter($results, fn($r) => $r === 'passed')) === count($results);

        return $allPassed ? 'verified' : 'under_review';
    }

    // ─── Mutations ────────────────────────────────────────────────────────────

    /**
     * Force a failure — may be called by the admin reviewer or by seeder/testing.
     */
    public function fail(string $reason = 'Identity verification failed.'): void
    {
        $this->update([
            'status'         => 'failed',
            'failure_reason' => $reason,
            'failed_at'      => now(),
        ]);
    }
}

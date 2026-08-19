<?php

namespace Nucleus\Kyc\Contracts;

use Nucleus\Kyc\Models\KycVerification;

interface KycProviderInterface
{
    /**
     * Unique slug identifier for this provider (e.g. 'stripe', 'onfido', 'manual').
     */
    public function name(): string;

    /**
     * Human-readable label (e.g. 'Stripe Identity').
     */
    public function label(): string;

    /**
     * Initiate a verification session with the provider.
     *
     * Returns an array with:
     *   - 'reference'  string   Provider-assigned session / applicant ID
     *   - 'url'        ?string  Redirect URL for hosted flows (null for in-app flows)
     *   - 'token'      ?string  Client-side SDK token for embedded flows
     *   - 'metadata'   array    Any additional provider-specific data
     */
    public function initiateVerification(KycVerification $verification): array;

    /**
     * Handle a webhook payload delivered by the provider.
     * Should update the KycVerification status accordingly.
     *
     * @param  array  $payload   Raw decoded payload from the provider
     * @param  KycVerification  $verification
     */
    public function handleWebhook(array $payload, KycVerification $verification): void;

    /**
     * Query the provider API for the current status of a verification.
     *
     * Returns an array with at least:
     *   - 'status'   string   Normalised status: pending|reviewing|verified|failed
     *   - 'raw'      array    Raw provider response
     */
    public function checkStatus(string $providerReference): array;

    /**
     * Whether this provider performs liveness detection natively
     * (so the manual liveness step can be skipped).
     */
    public function supportsLiveness(): bool;

    /**
     * Whether this provider handles document collection natively
     * (so the manual document upload steps can be skipped).
     */
    public function supportsDocumentCollection(): bool;
}

<?php

namespace Nucleus\Onboarding\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Nucleus\Onboarding\Events\OnboardingCompleted;
use Nucleus\Onboarding\Models\OnboardingSubmission;
use Nucleus\Onboarding\Services\AddressLookup\Contracts\AddressLookupProvider;

/**
 * Generic three-step onboarding wizard:
 *  1. Personal details
 *  2. Address (with optional postcode lookup + proof-of-address upload)
 *  3. Signature + (optional) document approval
 *
 * Industry-specific data goes into the OnboardingSubmission.metadata json
 * blob via fork-side subclassing or via listeners on OnboardingCompleted.
 */
class OnboardingWizard extends Component
{
    use WithFileUploads;

    public int $step = 1;
    public bool $manualAddress = false;
    public bool $submitted = false;
    public array $addressResults = [];
    public string $selectedAddressId = '';

    // Step 1 — Personal details
    public string $firstName = '';
    public string $lastName = '';
    public string $previousName = '';
    public string $gender = '';
    public string $dobDay = '';
    public string $dobMonth = '';
    public string $dobYear = '';
    public string $mobile = '';
    public string $nationalId = '';

    // Step 2 — Address
    public string $postcode = '';
    public string $houseNumber = '';
    public string $addressLine1 = '';
    public string $addressLine2 = '';
    public string $city = '';
    public $proofFile = null;

    // Step 3 — Signature + (optional) document approval
    public string $signatureData = '';
    public string $documentApprovalContent = '';

    public function mount(): void
    {
        $submission = OnboardingSubmission::where('user_id', auth()->id())->first();

        if ($submission && ! $submission->isSubmitted()) {
            $this->fillFromSubmission($submission);
        }
    }

    public function next(): void
    {
        $this->validateStep($this->step);

        if ($this->step === 2 && ! $this->manualAddress) {
            $this->manualAddress = true;
            $this->saveProgress();
            return;
        }

        $this->step++;
        $this->saveProgress();
    }

    public function back(): void
    {
        if ($this->step === 2 && $this->manualAddress) {
            $this->manualAddress = false;
            return;
        }

        $this->step = max(1, $this->step - 1);
    }

    public function submit(): void
    {
        $this->validate(['signatureData' => 'required|string|min:10'], [
            'signatureData.required' => 'Please sign before completing.',
        ]);

        $submission = $this->saveProgress(submitted: true);
        $this->submitted = true;

        OnboardingCompleted::dispatch(auth()->user(), $submission);

        $this->redirect(config('onboarding.completion_redirect', '/dashboard'));
    }

    // ─────────────────────────────────────────────────────────────────
    // Address lookup
    // ─────────────────────────────────────────────────────────────────

    public function lookupAddress(AddressLookupProvider $provider): void
    {
        $this->validate(['postcode' => 'required|string|min:5|max:10'], [
            'postcode.required' => 'Please enter a postcode to search.',
        ]);

        $this->addressResults = $provider->searchAddress($this->postcode);

        if (empty($this->addressResults)) {
            $this->addError('postcode', 'No addresses found. Try entering manually.');
        }
    }

    public function selectAddress(): void
    {
        if (! $this->selectedAddressId) {
            return;
        }

        $selected = collect($this->addressResults)->firstWhere('id', $this->selectedAddressId);

        if ($selected) {
            $this->houseNumber  = $selected['houseNumber'] ?? '';
            $this->addressLine1 = $selected['addressLine1'] ?? '';
            $this->addressLine2 = $selected['addressLine2'] ?? '';
            $this->city         = $selected['city'] ?? '';
            $this->manualAddress = true;
            $this->saveProgress();
        }
    }

    public function render()
    {
        $currentYear = (int) date('Y');

        return view('onboarding::livewire.onboarding-wizard', [
            'requireDocumentApproval' => (bool) config('onboarding.require_document_approval', false),
            'dobDays'                 => range(1, 31),
            'dobYears'                => range($currentYear - 18, 1930),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // Internals
    // ─────────────────────────────────────────────────────────────────

    private function validateStep(int $step): void
    {
        match ($step) {
            1 => $this->validate([
                'firstName' => 'required|string|max:100',
                'lastName'  => 'required|string|max:100',
                'dobDay'    => 'required',
                'dobMonth'  => 'required',
                'dobYear'   => 'required',
            ]),
            2 => $this->manualAddress
                ? $this->validate([
                    'postcode'     => 'required|string|max:10',
                    'addressLine1' => 'required|string|max:200',
                    'city'         => 'required|string|max:100',
                ])
                : $this->validate(['postcode' => 'required|string|max:10']),
            default => null,
        };
    }

    private function saveProgress(bool $submitted = false): OnboardingSubmission
    {
        $proofPath = $this->proofFile
            ? $this->proofFile->store('proof-of-address', 'public')
            : null;

        if ($this->firstName && $this->lastName) {
            $user = auth()->user();
            $newName = trim("{$this->firstName} {$this->lastName}");
            if ($user && $user->name !== $newName) {
                $user->name = $newName;
                $user->save();
            }
        }

        return OnboardingSubmission::updateOrCreate(
            ['user_id' => auth()->id()],
            array_filter([
                'current_step'              => $this->step,
                'status'                    => $submitted ? 'submitted' : 'in_progress',
                'first_name'                => $this->firstName ?: null,
                'last_name'                 => $this->lastName ?: null,
                'previous_name'             => $this->previousName ?: null,
                'gender'                    => $this->gender ?: null,
                'dob_day'                   => $this->dobDay ?: null,
                'dob_month'                 => $this->dobMonth ?: null,
                'dob_year'                  => $this->dobYear ?: null,
                'mobile'                    => $this->mobile ?: null,
                'national_id'               => $this->nationalId ?: null,
                'address_postcode'          => $this->postcode ?: null,
                'address_house_number'      => $this->houseNumber ?: null,
                'address_line_1'            => $this->addressLine1 ?: null,
                'address_line_2'            => $this->addressLine2 ?: null,
                'address_city'              => $this->city ?: null,
                'proof_of_address_path'     => $proofPath,
                'signature_data'            => $this->signatureData ?: null,
                'document_approval_content' => $this->documentApprovalContent ?: null,
                'submitted_at'              => $submitted ? now() : null,
            ], fn ($v) => $v !== null),
        );
    }

    private function fillFromSubmission(OnboardingSubmission $s): void
    {
        $this->step          = $s->current_step;
        $this->firstName     = $s->first_name ?? '';
        $this->lastName      = $s->last_name ?? '';
        $this->previousName  = $s->previous_name ?? '';
        $this->gender        = $s->gender ?? '';
        $this->dobDay        = $s->dob_day ? (string) $s->dob_day : '';
        $this->dobMonth      = $s->dob_month ?? '';
        $this->dobYear       = $s->dob_year ? (string) $s->dob_year : '';
        $this->mobile        = $s->mobile ?? '';
        $this->nationalId    = $s->national_id ?? '';
        $this->postcode      = $s->address_postcode ?? '';
        $this->houseNumber   = $s->address_house_number ?? '';
        $this->addressLine1  = $s->address_line_1 ?? '';
        $this->addressLine2  = $s->address_line_2 ?? '';
        $this->city          = $s->address_city ?? '';

        if ($s->address_line_1) {
            $this->manualAddress = true;
        }
    }
}

<?php

namespace Nucleus\Kyc\Livewire;

use Livewire\Component;
use Nucleus\Kyc\Events\KycSubmittedForReview;
use Nucleus\Kyc\Models\KycVerification;

class KycStatus extends Component
{
    public KycVerification $verification;

    public function mount(): void
    {
        $this->verification = KycVerification::firstOrCreate(
            ['user_id' => auth()->id()],
            ['status'  => 'pending', 'provider' => config('kyc.default_provider', 'manual')]
        );
    }

    /**
     * Called every 5 seconds by Livewire polling.
     * Only refreshes the record to detect admin status changes — no automatic advancement.
     */
    public function poll(): void
    {
        $this->verification->refresh();

        if ($this->verification->isVerified()) {
            $this->redirect('/dashboard', navigate: true);
        }
    }

    /**
     * User explicitly submits their documents for admin review.
     */
    public function submitForReview(): void
    {
        $this->verification->refresh();

        if (! $this->verification->canBeSubmitted()) {
            return;
        }

        $this->verification->update([
            'status'       => 'under_review',
            'submitted_at' => now(),
        ]);

        $this->verification->refresh();

        event(new KycSubmittedForReview($this->verification));
    }

    public function render()
    {
        return view('kyc::livewire.kyc-status');
    }
}

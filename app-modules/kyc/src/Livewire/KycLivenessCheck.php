<?php

namespace Nucleus\Kyc\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Nucleus\Kyc\Models\KycVerification;

class KycLivenessCheck extends Component
{
    use WithFileUploads;

    public int $step = 1;
    public $video;
    public KycVerification $verification;

    public function mount(): void
    {
        $this->verification = KycVerification::firstOrCreate(
            ['user_id' => auth()->id()],
            ['status'  => 'pending']
        );

        if ($this->verification->hasLivenessCheck()) {
            $this->step = 4;
        }
    }

    public function saveVideo(): void
    {
        $this->validate([
            'video' => 'required|file|mimetypes:video/webm,video/mp4|max:51200',
        ], [
            'video.required'  => 'Please record a video first.',
            'video.mimetypes' => 'Only WebM and MP4 video formats are accepted.',
            'video.max'       => 'Video must be smaller than 50 MB.',
        ]);

        $disk = config('kyc.storage_disk', 'kyc');
        $path = $this->video->store('kyc/liveness', $disk);

        $this->verification->update([
            'liveness_video_path'  => $path,
            'liveness_completed_at' => now(),
        ]);

        $this->verification->refresh();
        $this->step = 4;
    }

    public function retake(): void
    {
        $this->video = null;
        $this->step = 2;
    }

    public function render()
    {
        return view('kyc::livewire.kyc-liveness-check');
    }
}

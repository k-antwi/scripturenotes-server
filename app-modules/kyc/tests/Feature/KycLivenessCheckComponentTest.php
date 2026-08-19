<?php

use App\Models\User;
use Livewire\Livewire;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Nucleus\Kyc\Models\KycVerification;
use Nucleus\Kyc\Livewire\KycLivenessCheck;

beforeEach(function () {
    $this->user = User::factory()->create();
    Storage::fake('public');
});

it('mounts properly and creates a pending verification if needed', function () {
    Livewire::actingAs($this->user)
        ->test(KycLivenessCheck::class)
        ->assertSet('step', 1);

    $this->assertDatabaseHas('kyc_verifications', [
        'user_id' => $this->user->id,
        'status'  => 'pending',
    ]);
});

it('mounts directly to step 4 if liveness already completed', function () {
    KycVerification::create([
        'user_id' => $this->user->id,
        'status'  => 'pending',
        'liveness_video_path' => 'kyc/liveness/test.webm',
        'liveness_completed_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(KycLivenessCheck::class)
        ->assertSet('step', 4);
});

it('validates video upload', function () {
    Livewire::actingAs($this->user)
        ->test(KycLivenessCheck::class)
        ->set('step', 3) // Move to review step where upload happens
        ->call('saveVideo')
        ->assertHasErrors(['video' => 'required']);

    // Test mime type limit (must be webm or mp4)
    $video = UploadedFile::fake()->create('document.txt', 100, 'text/plain');
    Livewire::actingAs($this->user)
        ->test(KycLivenessCheck::class)
        ->set('step', 3)
        ->set('video', $video)
        ->call('saveVideo')
        ->assertHasErrors(['video' => 'mimetypes']);
});

it('successfully uploads and stores liveness video in database', function () {
    $video = UploadedFile::fake()->create('video.webm', 1024, 'video/webm');

    Livewire::actingAs($this->user)
        ->test(KycLivenessCheck::class)
        ->set('step', 3)
        ->set('video', $video)
        ->call('saveVideo')
        ->assertHasNoErrors()
        ->assertSet('step', 4);

    $verification = KycVerification::where('user_id', $this->user->id)->first();

    expect($verification->liveness_video_path)->not->toBeNull();
    expect($verification->liveness_completed_at)->not->toBeNull();

    Storage::disk('public')->assertExists($verification->liveness_video_path);
});

it('can retake video by resetting the step', function () {
    Livewire::actingAs($this->user)
        ->test(KycLivenessCheck::class)
        ->set('step', 3)
        ->call('retake')
        ->assertSet('step', 2)
        ->assertSet('video', null);
});

<?php

use App\Models\User;
use Nucleus\Kyc\Models\KycVerification;

beforeEach(function () {
    $this->user = User::where('email', 'admin@admin.com')->first();

    // Ensure a verified KYC record exists so the user can access the dashboard
    $this->verification = KycVerification::updateOrCreate(
        ['user_id' => $this->user->id],
        [
            'status'  => 'verified',
            'proof_of_address_path' => null,
            'photo_id_path' => null,
            'name_change_path' => null,
            'liveness_video_path' => null,
            'liveness_completed_at' => null,
        ]
    );
});

it('displays action items if documents are missing', function () {
    $this->actingAs($this->user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Action Required')
        ->assertSee('Proof of address')
        ->assertSee('Proof of identity')
        ->assertSee('Proof of name change');
});

it('displays liveness check if missing', function () {
    // Complete documents so liveness check is required
    $this->verification->update([
        'proof_of_address_path' => 'doc.pdf',
        'photo_id_path' => 'id.jpg',
        'name_change_path' => 'name.pdf',
    ]);

    $this->actingAs($this->user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Liveness verification');
});

it('does not display action items if all are completed', function () {
    // Complete all actions
    $this->verification->update([
        'proof_of_address_path' => 'doc.pdf',
        'photo_id_path' => 'id.jpg',
        'name_change_path' => 'name.pdf',
        'liveness_video_path' => 'video.webm',
        'liveness_completed_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee('Action Required')
        ->assertDontSee('Upload Proof of Address')
        ->assertDontSee('Upload Photo ID')
        ->assertDontSee('Upload Proof of Name Change')
        ->assertDontSee('Liveness Video Check');
});

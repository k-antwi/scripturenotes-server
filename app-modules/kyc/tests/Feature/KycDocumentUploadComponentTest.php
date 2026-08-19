<?php

use App\Models\User;
use Livewire\Livewire;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Nucleus\Kyc\Models\KycVerification;
use Nucleus\Kyc\Livewire\KycDocumentUpload;

beforeEach(function () {
    $this->user = User::factory()->create();
    Storage::fake('kyc');
});

it('fails to mount with invalid document type', function () {
    Livewire::actingAs($this->user)
        ->test(KycDocumentUpload::class, ['type' => 'invalid_type'])
        ->assertStatus(404);
});

it('mounts properly and creates a pending verification if needed', function () {
    Livewire::actingAs($this->user)
        ->test(KycDocumentUpload::class, ['type' => 'proof_of_address'])
        ->assertSet('step', 1)
        ->assertSet('type', 'proof_of_address');

    $this->assertDatabaseHas('kyc_verifications', [
        'user_id' => $this->user->id,
        'status'  => 'pending',
    ]);
});

it('mounts directly to step 2 if document already uploaded', function () {
    KycVerification::create([
        'user_id'               => $this->user->id,
        'status'                => 'pending',
        'proof_of_address_path' => 'kyc/proof_of_address/test.pdf',
    ]);

    Livewire::actingAs($this->user)
        ->test(KycDocumentUpload::class, ['type' => 'proof_of_address'])
        ->assertSet('step', 2);
});

it('validates file upload', function () {
    // Test required
    Livewire::actingAs($this->user)
        ->test(KycDocumentUpload::class, ['type' => 'proof_of_address'])
        ->call('uploadDocument')
        ->assertHasErrors(['file' => 'required']);

    // Test invalid mime type
    $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');
    Livewire::actingAs($this->user)
        ->test(KycDocumentUpload::class, ['type' => 'proof_of_address'])
        ->set('file', $file)
        ->call('uploadDocument')
        ->assertHasErrors(['file' => 'mimes']);

    // Test size limit (6MB)
    $file = UploadedFile::fake()->create('document.pdf', 6000, 'application/pdf');
    Livewire::actingAs($this->user)
        ->test(KycDocumentUpload::class, ['type' => 'proof_of_address'])
        ->set('file', $file)
        ->call('uploadDocument')
        ->assertHasErrors(['file' => 'max']);
});

it('successfully uploads and stores file path in database on the kyc disk', function () {
    $file = UploadedFile::fake()->image('passport.jpg');

    Livewire::actingAs($this->user)
        ->test(KycDocumentUpload::class, ['type' => 'photo_id'])
        ->set('file', $file)
        ->call('uploadDocument')
        ->assertHasNoErrors()
        ->assertSet('step', 2);

    $verification = KycVerification::where('user_id', $this->user->id)->first();

    expect($verification->photo_id_path)->not->toBeNull();
    expect($verification->photo_id_uploaded_at)->not->toBeNull();

    // File must be on the private 'kyc' disk, not the public disk
    Storage::disk('kyc')->assertExists($verification->photo_id_path);
});

it('can remove an uploaded document', function () {
    $verification = KycVerification::create([
        'user_id'                 => $this->user->id,
        'status'                  => 'pending',
        'name_change_path'        => 'kyc/name_change/old_doc.pdf',
        'name_change_uploaded_at' => now(),
    ]);

    Livewire::actingAs($this->user)
        ->test(KycDocumentUpload::class, ['type' => 'name_change'])
        ->assertSet('step', 2)
        ->call('removeDocument')
        ->assertSet('step', 1)
        ->assertSet('file', null);

    $verification->refresh();
    expect($verification->name_change_path)->toBeNull();
    expect($verification->name_change_uploaded_at)->toBeNull();
});

it('can confirm document and move to success step', function () {
    KycVerification::create([
        'user_id'               => $this->user->id,
        'status'                => 'pending',
        'proof_of_address_path' => 'kyc/proof_of_address/test.pdf',
    ]);

    Livewire::actingAs($this->user)
        ->test(KycDocumentUpload::class, ['type' => 'proof_of_address'])
        ->assertSet('step', 2)
        ->call('confirmDocument')
        ->assertSet('step', 3);
});

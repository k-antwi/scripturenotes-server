<?php

namespace Nucleus\Kyc\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Nucleus\Kyc\Models\KycVerification;

class KycDocumentUpload extends Component
{
    use WithFileUploads;

    public string $type;
    public int $step = 1;
    public $file;
    public KycVerification $verification;

    private array $tips = [
        'proof_of_address' => [
            'what' => [
                'A council tax statement or bill',
                'A bank, building society, or credit union statement or passbook',
                'A mortgage statement',
                'A utility bill (gas, electric, water, phone)',
            ],
            'keep_in_mind' => [
                'Documents must be recent (within the last 3 months)',
                'The address must match your current registered address',
                'Screenshots or photos of computer screens are not accepted',
                'All four corners of the document must be visible',
            ],
        ],
        'photo_id' => [
            'what' => [
                'A valid passport (photo page)',
                'A UK driving licence (front and back)',
                'A national identity card',
                'A biometric residence permit',
            ],
            'keep_in_mind' => [
                'The ID must not be expired',
                'The photo must be clear and unobscured',
                'All details must be legible',
                'All four corners of the document must be visible',
            ],
        ],
        'name_change' => [
            'what' => [
                'A marriage or civil partnership certificate',
                'A decree absolute or final order',
                'A deed poll',
                'A statutory declaration of name change',
            ],
            'keep_in_mind' => [
                'The document must be an official or certified copy',
                'Both your previous and current names must be visible',
                'All four corners of the document must be visible',
                'Screenshots or photos of computer screens are not accepted',
            ],
        ],
    ];

    public function mount(string $type): void
    {
        abort_unless(array_key_exists($type, KycVerification::DOCUMENT_TYPES), 404);

        $this->type = $type;
        $this->verification = KycVerification::firstOrCreate(
            ['user_id' => auth()->id()],
            ['status'  => 'pending']
        );

        if ($this->verification->hasDocument($type)) {
            $this->step = 2;
        }
    }

    public function uploadDocument(): void
    {
        $this->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'file.required' => 'Please select a file to upload.',
            'file.mimes'    => 'Only JPG, PNG and PDF files are accepted.',
            'file.max'      => 'File must be smaller than 5 MB.',
        ]);

        $config = KycVerification::DOCUMENT_TYPES[$this->type];
        $disk   = config('kyc.storage_disk', 'kyc');
        $path   = $this->file->store("kyc/{$this->type}", $disk);

        $this->verification->update([
            $config['path_col'] => $path,
            $config['date_col'] => now(),
        ]);

        $this->verification->refresh();
        $this->step = 2;
    }

    public function removeDocument(): void
    {
        $config = KycVerification::DOCUMENT_TYPES[$this->type];

        $this->verification->update([
            $config['path_col'] => null,
            $config['date_col'] => null,
        ]);

        $this->verification->refresh();
        $this->file = null;
        $this->step = 1;
    }

    public function confirmDocument(): void
    {
        $this->step = 3;
    }

    public function label(): string
    {
        return KycVerification::DOCUMENT_TYPES[$this->type]['label'] ?? '';
    }

    public function tipsFor(): array
    {
        return $this->tips[$this->type] ?? [];
    }

    public function storedFilePath(): ?string
    {
        $config = KycVerification::DOCUMENT_TYPES[$this->type];

        return $this->verification->{$config['path_col']};
    }

    public function isImage(): bool
    {
        $path = $this->storedFilePath();

        return $path && preg_match('/\.(jpg|jpeg|png)$/i', $path);
    }

    public function render()
    {
        return view('kyc::livewire.kyc-document-upload', [
            'label'    => $this->label(),
            'tips'     => $this->tipsFor(),
            'filePath' => $this->storedFilePath(),
            'isImage'  => $this->isImage(),
        ]);
    }
}

<?php

namespace Nucleus\Kyc\Filament\Resources\KycVerificationResource\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Nucleus\Kyc\Filament\Resources\KycVerificationResource;
use Nucleus\Kyc\Models\KycVerification;

class EditKycVerification extends EditRecord
{
    protected static string $resource = KycVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('saveDecision')
                ->label('Save decision')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Save review decision')
                ->modalDescription('This will update the verification status and notify the applicant by email.')
                ->action(function () {
                    /** @var KycVerification $record */
                    $record = $this->getRecord();

                    // Mark review as started if first time
                    if (! $record->review_started_at) {
                        $record->update([
                            'review_started_at' => now(),
                            'reviewed_by'       => auth()->id(),
                        ]);
                    }

                    KycVerificationResource::saveDecision($record, $this->form->getState());

                    Notification::make()
                        ->title('Decision saved')
                        ->body('The applicant has been notified by email.')
                        ->success()
                        ->send();

                    $this->redirect(KycVerificationResource::getUrl('index'));
                }),
        ];
    }

    protected function getFormActions(): array
    {
        // Hide the default save button — we use the "Save decision" header action
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var KycVerification $record */
        $record = $this->getRecord();

        // Set review_started_at and reviewed_by on first open
        if (! $record->review_started_at) {
            $record->update([
                'review_started_at' => now(),
                'reviewed_by'       => auth()->id(),
            ]);
        }

        return $data;
    }
}

<?php

namespace Nucleus\Kyc\Filament\Resources;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Nucleus\Kyc\Events\KycFailed;
use Nucleus\Kyc\Events\KycMoreInfoRequested;
use Nucleus\Kyc\Events\KycVerified;
use Nucleus\Kyc\Filament\Resources\KycVerificationResource\Pages\EditKycVerification;
use Nucleus\Kyc\Filament\Resources\KycVerificationResource\Pages\ListKycVerifications;
use Nucleus\Kyc\Models\KycVerification;

class KycVerificationResource extends Resource
{
    protected static ?string $model = KycVerification::class;

    protected static BackedEnum|string|null $navigationIcon = 'phosphor-identification-card-duotone';

    protected static ?string $navigationLabel = 'KYC Review Queue';

    protected static string|\UnitEnum|null $navigationGroup = 'Compliance';

    protected static ?int $navigationSort = 10;

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── Proof of Address ────────────────────────────────────────────
            Section::make('Proof of address')
                ->columns(1)
                ->schema([
                    Placeholder::make('proof_of_address_preview')
                        ->label('Uploaded document')
                        ->content(fn (KycVerification $record) => self::filePreviewHtml(
                            $record->proof_of_address_path,
                            'proof_of_address'
                        )),

                    Radio::make('proof_of_address_result')
                        ->label('Result')
                        ->options([
                            'pending'   => 'Pending',
                            'passed'    => 'Passed',
                            'failed'    => 'Failed',
                            'more_info' => 'More information needed',
                        ])
                        ->required(),

                    Textarea::make('proof_of_address_note')
                        ->label('Reviewer note')
                        ->rows(2)
                        ->placeholder('Optional note for the applicant…'),
                ]),

            // ── Photo ID ────────────────────────────────────────────────────
            Section::make('Proof of identity')
                ->columns(1)
                ->schema([
                    Placeholder::make('photo_id_preview')
                        ->label('Uploaded document')
                        ->content(fn (KycVerification $record) => self::filePreviewHtml(
                            $record->photo_id_path,
                            'photo_id'
                        )),

                    Radio::make('photo_id_result')
                        ->label('Result')
                        ->options([
                            'pending'   => 'Pending',
                            'passed'    => 'Passed',
                            'failed'    => 'Failed',
                            'more_info' => 'More information needed',
                        ])
                        ->required(),

                    Textarea::make('photo_id_note')
                        ->label('Reviewer note')
                        ->rows(2)
                        ->placeholder('Optional note for the applicant…'),
                ]),

            // ── Name Change (only if document uploaded) ──────────────────────
            Section::make('Proof of name change')
                ->columns(1)
                ->visible(fn (KycVerification $record) => $record->hasDocument('name_change'))
                ->schema([
                    Placeholder::make('name_change_preview')
                        ->label('Uploaded document')
                        ->content(fn (KycVerification $record) => self::filePreviewHtml(
                            $record->name_change_path,
                            'name_change'
                        )),

                    Radio::make('name_change_result')
                        ->label('Result')
                        ->options([
                            'pending'   => 'Pending',
                            'passed'    => 'Passed',
                            'failed'    => 'Failed',
                            'more_info' => 'More information needed',
                        ]),

                    Textarea::make('name_change_note')
                        ->label('Reviewer note')
                        ->rows(2)
                        ->placeholder('Optional note for the applicant…'),
                ]),

            // ── Liveness ────────────────────────────────────────────────────
            Section::make('Liveness check')
                ->columns(1)
                ->schema([
                    Placeholder::make('liveness_preview')
                        ->label('Recorded video')
                        ->content(fn (KycVerification $record) => self::filePreviewHtml(
                            $record->liveness_video_path,
                            'liveness'
                        )),

                    Radio::make('liveness_result')
                        ->label('Result')
                        ->options([
                            'pending'   => 'Pending',
                            'passed'    => 'Passed',
                            'failed'    => 'Failed',
                            'more_info' => 'More information needed',
                        ])
                        ->required(),

                    Textarea::make('liveness_note')
                        ->label('Reviewer note')
                        ->rows(2)
                        ->placeholder('Optional note for the applicant…'),
                ]),

            // ── Overall decision ────────────────────────────────────────────
            Section::make('Overall decision')
                ->columns(1)
                ->schema([
                    Placeholder::make('derived_result')
                        ->label('Auto-derived result')
                        ->content(fn (KycVerification $record) => $record->deriveOverallResult()),

                    Select::make('status')
                        ->label('Override status (optional)')
                        ->options([
                            'under_review'     => 'Under review (no change)',
                            'verified'         => 'Verified',
                            'failed'           => 'Failed',
                            'more_info_needed' => 'More information needed',
                        ])
                        ->placeholder('Use auto-derived result'),

                    Textarea::make('overall_review_note')
                        ->label('Overall note')
                        ->rows(3)
                        ->placeholder('This note will be included in the notification email to the applicant…'),
                ]),
        ]);
    }

    // ─── Table ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Applicant')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('days_waiting')
                    ->label('Days waiting')
                    ->state(fn (KycVerification $record) => $record->submitted_at
                        ? (int) $record->submitted_at->diffInDays(now())
                        : null
                    )
                    ->suffix(' days')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('submitted_at', $direction === 'asc' ? 'desc' : 'asc')),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'verified'         => 'success',
                        'failed'           => 'danger',
                        'under_review'     => 'warning',
                        'more_info_needed' => 'info',
                        default            => 'gray',
                    }),

                TextColumn::make('proof_of_address_result')
                    ->label('Address')
                    ->badge()
                    ->color(fn (string $state) => self::resultColor($state)),

                TextColumn::make('photo_id_result')
                    ->label('Photo ID')
                    ->badge()
                    ->color(fn (string $state) => self::resultColor($state)),

                TextColumn::make('liveness_result')
                    ->label('Liveness')
                    ->badge()
                    ->color(fn (string $state) => self::resultColor($state)),
            ])
            ->defaultSort('submitted_at', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'          => 'Pending',
                        'documents_ready'  => 'Documents ready',
                        'under_review'     => 'Under review',
                        'more_info_needed' => 'More info needed',
                        'verified'         => 'Verified',
                        'failed'           => 'Failed',
                    ])
                    ->default(['under_review', 'more_info_needed'])
                    ->multiple(),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()
                    ->label('Review'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKycVerifications::route('/'),
            'edit'  => EditKycVerification::route('/{record}/edit'),
        ];
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Generate an HTML preview snippet for a stored file.
     * Uses temporaryUrl() when supported; falls back to the private file-serve route.
     */
    public static function filePreviewHtml(?string $path, string $type): string
    {
        if (! $path) {
            return '<span class="text-gray-400 text-sm">No file uploaded</span>';
        }

        $disk = config('kyc.storage_disk', 'kyc');
        $url  = null;

        try {
            $url = Storage::disk($disk)->temporaryUrl($path, now()->addMinutes(15));
        } catch (\Throwable) {
            $url = route('kyc.files', ['path' => $path]);
        }

        $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $path);
        $isVideo = preg_match('/\.(webm|mp4|mov)$/i', $path);

        if ($isImage) {
            return '<a href="' . e($url) . '" target="_blank">'
                . '<img src="' . e($url) . '" style="max-width:320px;max-height:240px;border-radius:6px;border:1px solid #e5e7eb;" />'
                . '</a>';
        }

        if ($isVideo) {
            return '<video controls style="max-width:320px;border-radius:6px;border:1px solid #e5e7eb;">'
                . '<source src="' . e($url) . '">'
                . 'Your browser does not support video playback.'
                . '</video>';
        }

        $filename = basename($path);

        return '<a href="' . e($url) . '" target="_blank" class="text-primary-600 underline text-sm">'
            . e($filename)
            . ' (download)</a>';
    }

    private static function resultColor(string $state): string
    {
        return match ($state) {
            'passed'    => 'success',
            'failed'    => 'danger',
            'more_info' => 'warning',
            default     => 'gray',
        };
    }

    /**
     * Persist a reviewer decision, derive the overall outcome, and fire the
     * appropriate event. Called by the edit page's "Save decision" action.
     */
    public static function saveDecision(KycVerification $record, array $data): void
    {
        // Save per-check results and notes
        $record->update([
            'proof_of_address_result' => $data['proof_of_address_result'],
            'proof_of_address_note'   => $data['proof_of_address_note'] ?? null,
            'photo_id_result'         => $data['photo_id_result'],
            'photo_id_note'           => $data['photo_id_note'] ?? null,
            'name_change_result'      => $data['name_change_result'] ?? 'pending',
            'name_change_note'        => $data['name_change_note'] ?? null,
            'liveness_result'         => $data['liveness_result'],
            'liveness_note'           => $data['liveness_note'] ?? null,
            'overall_review_note'     => $data['overall_review_note'] ?? null,
            'reviewed_by'             => auth()->id(),
            'review_completed_at'     => now(),
        ]);

        // Use manual override if provided, otherwise auto-derive
        $newStatus = (! empty($data['status']) && $data['status'] !== 'under_review')
            ? $data['status']
            : $record->deriveOverallResult();

        // Apply the new status and set appropriate timestamps
        $updates = ['status' => $newStatus];

        match ($newStatus) {
            'verified' => $updates['identity_verified_at'] = now(),
            'failed'   => $updates['failed_at'] = now(),
            default    => null,
        };

        if ($newStatus === 'more_info_needed') {
            // Clear document paths for checks that need re-uploading
            foreach ($record->checksNeedingAttention() as $type => $check) {
                $docConfig = KycVerification::DOCUMENT_TYPES[$type] ?? null;

                if ($docConfig) {
                    $updates[$docConfig['path_col']] = null;
                    $updates[$docConfig['date_col']] = null;
                }

                // Reset the check result back to pending for re-upload
                $updates[KycVerification::CHECK_TYPES[$type]['result_col']] = 'pending';
            }
        }

        $record->update($updates);

        // Fire the appropriate event
        match ($newStatus) {
            'verified'         => event(new KycVerified($record->fresh())),
            'failed'           => event(new KycFailed($record->fresh())),
            'more_info_needed' => event(new KycMoreInfoRequested($record->fresh())),
            default            => null,
        };
    }
}

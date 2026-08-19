<?php

namespace Nucleus\Brain\Filament\Resources\AiModelResource;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Nucleus\Brain\Filament\Resources\AiModelResource\Pages\CreateAiModel;
use Nucleus\Brain\Filament\Resources\AiModelResource\Pages\EditAiModel;
use Nucleus\Brain\Filament\Resources\AiModelResource\Pages\ListAiModels;
use Nucleus\Brain\Models\AiModel;

class AiModelResource extends Resource
{
    protected static ?string $model = AiModel::class;

    protected static BackedEnum|string|null $navigationIcon = 'phosphor-brain-duotone';

    protected static ?string $navigationLabel = 'AI Models';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Intel';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')
                ->label('Display Name')
                ->required()
                ->maxLength(191)
                ->placeholder('e.g. Claude Sonnet 4.6'),

            Select::make('provider')
                ->label('Provider')
                ->required()
                ->options(AiModel::providerOptions())
                ->searchable()
                ->createOptionForm([
                    TextInput::make('key')
                        ->label('Provider Identifier')
                        ->required()
                        ->placeholder('e.g. azure, cohere, ollama')
                        ->helperText('Lowercase, no spaces. Must match the driver name expected by the Laravel AI SDK.')
                        ->rules(['regex:/^[a-z0-9_-]+$/']),
                ])
                ->createOptionUsing(fn (array $data): string => $data['key']),

            TextInput::make('model_id')
                ->label('Model ID')
                ->required()
                ->maxLength(191)
                ->placeholder('e.g. claude-sonnet-4-6, gpt-4o'),

            TextInput::make('api_key')
                ->label('API Key')
                ->required()
                ->password()
                ->revealable()
                ->maxLength(512)
                ->helperText('Stored encrypted. Leave unchanged when editing to keep the existing key.')
                ->dehydrateStateUsing(fn ($state, $record) => filled($state) ? $state : ($record?->getRawOriginal('api_key') ?? $state)),

            Toggle::make('is_active')
                ->label('Set as Active Model')
                ->helperText('Only one model can be active at a time.'),

            KeyValue::make('config')
                ->label('Extra Config (optional)')
                ->keyLabel('Option')
                ->valueLabel('Value')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('provider')
                    ->label('Provider')
                    ->badge()
                    ->searchable(),

                TextColumn::make('model_id')
                    ->label('Model ID')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                Action::make('activate')
                    ->label('Set Active')
                    ->icon('phosphor-check-circle-duotone')
                    ->color('success')
                    ->visible(fn (AiModel $record) => ! $record->is_active)
                    ->requiresConfirmation()
                    ->action(function (AiModel $record) {
                        $record->activate();

                        Notification::make()
                            ->title("{$record->label} is now the active AI model.")
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAiModels::route('/'),
            'create' => CreateAiModel::route('/create'),
            'edit'   => EditAiModel::route('/{record}/edit'),
        ];
    }
}

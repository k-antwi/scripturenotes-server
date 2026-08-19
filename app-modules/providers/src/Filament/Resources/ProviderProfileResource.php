<?php

namespace Nucleus\Providers\Filament\Resources;

use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Nucleus\Providers\Filament\Resources\ProviderProfileResource\Pages\CreateProviderProfile;
use Nucleus\Providers\Filament\Resources\ProviderProfileResource\Pages\EditProviderProfile;
use Nucleus\Providers\Filament\Resources\ProviderProfileResource\Pages\ListProviderProfiles;
use Nucleus\Providers\Models\ProviderProfile;

class ProviderProfileResource extends Resource
{
    protected static ?string $model = ProviderProfile::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Providers';

    protected static ?string $modelLabel = 'Provider';

    protected static ?string $pluralModelLabel = 'Providers';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identity')->schema([
                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),

                Grid::make(2)->schema([
                    TextInput::make('credential_reference')
                        ->label('Credential reference')
                        ->maxLength(100)
                        ->helperText('External regulator/registry ID (FCA, BAR, NPI, etc.)'),

                    Select::make('verification_status')
                        ->options(ProviderProfile::statusOptions())
                        ->required(),
                ]),

                Textarea::make('verification_message')->rows(2),
            ]),

            Section::make('Profile')->schema([
                Textarea::make('bio')->rows(4),

                Grid::make(2)->schema([
                    Toggle::make('is_active')->default(true),
                    Toggle::make('is_primary')
                        ->helperText('"Primary" provider for routing/auto-assignment. Ignore in marketplaces.'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->searchable()->sortable(),
                TextColumn::make('credential_reference')->searchable()->placeholder('—'),
                TextColumn::make('verification_status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ProviderProfile::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        ProviderProfile::STATUS_VERIFIED      => 'success',
                        ProviderProfile::STATUS_FAILED        => 'danger',
                        ProviderProfile::STATUS_MANUAL_REVIEW => 'warning',
                        default                               => 'gray',
                    }),
                IconColumn::make('is_primary')->boolean(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('verified_at')->dateTime()->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('verification_status')->options(ProviderProfile::statusOptions()),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProviderProfiles::route('/'),
            'create' => CreateProviderProfile::route('/create'),
            'edit'   => EditProviderProfile::route('/{record}/edit'),
        ];
    }
}

<?php

namespace Nucleus\Organisations\Filament\Resources;

use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Nucleus\Organisations\Filament\Resources\OrganisationResource\Pages\CreateOrganisation;
use Nucleus\Organisations\Filament\Resources\OrganisationResource\Pages\EditOrganisation;
use Nucleus\Organisations\Filament\Resources\OrganisationResource\Pages\ListOrganisations;
use Nucleus\Organisations\Models\Organisation;

class OrganisationResource extends Resource
{
    protected static ?string $model = Organisation::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Organisations';

    protected static ?string $modelLabel = 'Organisation';

    protected static ?string $pluralModelLabel = 'Organisations';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic Information')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('legal_name')->maxLength(255),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('registration_number')->maxLength(100),
                    TextInput::make('tax_reference')->maxLength(100),
                ]),
                Grid::make(2)->schema([
                    Select::make('organisation_type')
                        ->options(Organisation::typeOptions())
                        ->required()
                        ->default('other'),
                    Select::make('status')
                        ->options(Organisation::statusOptions())
                        ->required()
                        ->default('active'),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('industry_code')
                        ->label('Industry classifier')
                        ->maxLength(20),
                    TextInput::make('employee_count')->numeric()->minValue(0),
                ]),
            ]),

            Section::make('Primary Contact')->collapsed()->schema([
                Grid::make(3)->schema([
                    TextInput::make('primary_contact_name')->maxLength(255),
                    TextInput::make('primary_contact_email')->email()->maxLength(255),
                    TextInput::make('primary_contact_phone')->maxLength(50),
                ]),
            ]),

            Section::make('Address')->collapsed()->schema([
                Grid::make(2)->schema([
                    TextInput::make('address_line_1')->maxLength(255),
                    TextInput::make('address_line_2')->maxLength(255),
                ]),
                Grid::make(3)->schema([
                    TextInput::make('city')->maxLength(100),
                    TextInput::make('county')->maxLength(100),
                    TextInput::make('postcode')->maxLength(20),
                ]),
                TextInput::make('country_code')
                    ->label('Country code (ISO 3166-1)')
                    ->maxLength(2)
                    ->default('GB'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('registration_number')->label('Reg. no.')->searchable()->placeholder('—'),
                TextColumn::make('organisation_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Organisation::typeOptions()[$state] ?? $state),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active'    => 'success',
                        'suspended' => 'warning',
                        'dissolved' => 'danger',
                        default     => 'gray',
                    }),
                TextColumn::make('city')->placeholder('—'),
                TextColumn::make('postcode')->placeholder('—'),
                TextColumn::make('created_at')->date()->sortable()->label('Added'),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('organisation_type')->options(Organisation::typeOptions()),
                SelectFilter::make('status')->options(Organisation::statusOptions()),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListOrganisations::route('/'),
            'create' => CreateOrganisation::route('/create'),
            'edit'   => EditOrganisation::route('/{record}/edit'),
        ];
    }
}

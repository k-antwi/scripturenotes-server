<?php

namespace Nucleus\Permit\Filament\Resources;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Nucleus\Permit\Filament\Resources\PermitResource\Pages\CreatePermit;
use Nucleus\Permit\Filament\Resources\PermitResource\Pages\EditPermit;
use Nucleus\Permit\Filament\Resources\PermitResource\Pages\ListPermits;
use Spatie\Permission\Models\Permission;

class PermitResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static BackedEnum|string|null $navigationIcon = 'phosphor-key-duotone';

    protected static ?string $navigationLabel = 'Permissions';

    protected static ?string $modelLabel = 'Permission';

    protected static ?string $pluralModelLabel = 'Permissions';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Permit';
    }

    /**
     * Convert a snake_case or dot-notation permission name to a human-readable label.
     */
    public static function humanReadable(string $name): string
    {
        // Replace dots and underscores with spaces, then title-case
        return Str::title(str_replace(['.', '_', '-'], ' ', $name));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Permission Name')
                ->helperText('Use snake_case or dot notation, e.g. view_users or users.view')
                ->required()
                ->maxLength(191)
                ->unique(ignoreRecord: true),
            TextInput::make('guard_name')
                ->label('Guard')
                ->default('web')
                ->required()
                ->maxLength(191),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Permission')
                    ->formatStateUsing(fn (string $state): string => static::humanReadable($state))
                    ->description(fn (Permission $record): string => $record->name)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('roles_count')
                    ->label('Assigned to Roles')
                    ->counts('roles')
                    ->badge()
                    ->color('info'),
                TextColumn::make('users_count')
                    ->label('Direct User Assignments')
                    ->counts('users')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('guard_name')
                    ->label('Guard')
                    ->options(fn () => Permission::query()->distinct()->pluck('guard_name', 'guard_name')->toArray()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermits::route('/'),
            'create' => CreatePermit::route('/create'),
            'edit' => EditPermit::route('/{record}/edit'),
        ];
    }
}

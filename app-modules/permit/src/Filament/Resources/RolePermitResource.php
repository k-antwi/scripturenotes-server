<?php

namespace Nucleus\Permit\Filament\Resources;

use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Nucleus\Permit\Filament\Resources\RolePermitResource\Pages\EditRolePermit;
use Nucleus\Permit\Filament\Resources\RolePermitResource\Pages\ListRolePermits;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermitResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static BackedEnum|string|null $navigationIcon = 'phosphor-shield-check-duotone';

    protected static ?string $navigationLabel = 'Role Permissions';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'Role Permissions';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Permit';
    }

    /**
     * Convert a snake_case or dot-notation permission name to a human-readable label.
     */
    public static function humanReadable(string $name): string
    {
        return Str::title(str_replace(['.', '_', '-'], ' ', $name));
    }

    public static function form(Schema $schema): Schema
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Permission $permission) => [
                $permission->id => static::humanReadable($permission->name),
            ])
            ->toArray();

        return $schema->components([
            TextInput::make('name')
                ->label('Role Name')
                ->disabled()
                ->dehydrated(false),
            TextInput::make('guard_name')
                ->label('Guard')
                ->disabled()
                ->dehydrated(false),
            CheckboxList::make('permissions')
                ->label('Assigned Permissions')
                ->relationship('permissions', 'name')
                ->options($permissions)
                ->searchable()
                ->bulkToggleable()
                ->columns(2)
                ->columnSpanFull()
                ->helperText('Select which permissions this role should have.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Role')
                    ->formatStateUsing(fn (string $state): string => static::humanReadable($state))
                    ->description(fn (Role $record): string => $record->description ?? '')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Manage Permissions'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRolePermits::route('/'),
            'edit' => EditRolePermit::route('/{record}/edit'),
        ];
    }
}

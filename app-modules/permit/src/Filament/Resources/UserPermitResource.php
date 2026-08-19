<?php

namespace Nucleus\Permit\Filament\Resources;

use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Nucleus\Permit\Filament\Resources\UserPermitResource\Pages\EditUserPermit;
use Nucleus\Permit\Filament\Resources\UserPermitResource\Pages\ListUserPermits;
use Spatie\Permission\Models\Permission;

class UserPermitResource extends Resource
{
    protected static ?string $model = User::class;

    protected static BackedEnum|string|null $navigationIcon = 'phosphor-user-gear-duotone';

    protected static ?string $navigationLabel = 'User Permissions';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'User Permissions';

    protected static ?int $navigationSort = 3;

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
            Placeholder::make('roles_info')
                ->label('Roles')
                ->content(fn (User $record): HtmlString => new HtmlString(
                    $record->roles->map(fn ($role) => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-1">'
                        . static::humanReadable($role->name) . '</span>'
                    )->implode('')
                    ?: '<span class="text-gray-400 text-sm">No roles assigned</span>'
                ))
                ->columnSpanFull(),
            CheckboxList::make('permissions')
                ->label('Direct Permissions')
                ->relationship('permissions', 'name')
                ->options($permissions)
                ->searchable()
                ->bulkToggleable()
                ->columns(2)
                ->columnSpanFull()
                ->helperText('These permissions are granted directly to the user, in addition to any permissions inherited from their roles.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->circular()
                    ->defaultImageUrl(url('storage/demo/default.png')),
                TextColumn::make('name')
                    ->label('User')
                    ->description(fn (User $record): string => $record->email)
                    ->searchable(),
                TextColumn::make('roles')
                    ->label('Roles')
                    ->formatStateUsing(fn ($state): string => static::humanReadable($state))
                    ->badge()
                    ->color('info')
                    ->separator(','),
                TextColumn::make('permissions_count')
                    ->label('Direct Permissions')
                    ->counts('permissions')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload(),
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
            'index' => ListUserPermits::route('/'),
            'edit' => EditUserPermit::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\TenantResource\RelationManagers;

use App\Enums\RoleEnum;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use Rawilk\FilamentPasswordInput\Password;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'tenantUsers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('middle_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->disabledOn('edit')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Password::make('password')
                    ->label(__("Password"))
                    ->revealable()
                    ->visibleOn('create')
                    ->regeneratePassword()
                    ->maxLength(10)
                    ->required(),
                Forms\Components\TextInput::make('phone_number')
                    ->label(__("Phone/Mobile Number"))
                    ->rule('phone')
                    ->hintAction(
                        Forms\Components\Actions\Action::make('mobile_number_info')
                            ->icon('heroicon-o-question-mark-circle')
                            ->tooltip(__('Number must be valid and in an international format. Ex. +9779843123456'))
                            ->label('')
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn($record) => $record->full_name)
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable([
                        'first_name',
                        'last_name'
                    ])
                    ->sortable(false),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(
                        function (User $record): void {
                            $record->assignRole(Role::where('name', RoleEnum::Tenant_user->value)->pluck('id'));
                        }
                    ),
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        return $query->role(RoleEnum::Tenant_user->value);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }

    public static function getRecordTitle($record): ?string
    {
        return $record->full_name; // Assuming this accessor is defined in your User model
    }
}

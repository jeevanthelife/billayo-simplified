<?php

namespace App\Filament\Resources;

use App\Enums\RoomStatusEnum;
use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'conference-room-28-o';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('house_id')
                    ->relationship('house', 'name')
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('room_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('meter_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('latest_meter_reading')
                    ->required()
                    ->numeric()
                    ->maxLength(255),
                Forms\Components\TextInput::make('rent_amount')
                    ->required()
                    ->numeric()
                    ->inputMode('decimal')
                    ->rules(['max:99999999.99']),
                Forms\Components\Select::make('tenant_id')
                    ->label(__('Occupant'))
                    ->relationship('tenant', 'name')
                    ->preload()
                    ->live()
                    ->reactive()
                    ->afterStateUpdated(
                        function (Get $get, Set $set) {
                            return $set('status', $get('tenant_id') ?
                                RoomStatusEnum::Occupied->value :
                                RoomStatusEnum::Available->value);
                        }
                    ),
                Forms\Components\Toggle::make('is_flat')
                    ->inline(false)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->options(RoomStatusEnum::class)
                    ->default(RoomStatusEnum::Available->value)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('house.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('room_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('meter_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latest_meter_reading')
                    ->badge(),
                Tables\Columns\TextColumn::make('rent_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label(__('Occupant'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_flat')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}

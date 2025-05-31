<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantRoomResource\Pages;
use App\Filament\Resources\TenantRoomResource\RelationManagers;
use App\Models\House;
use App\Models\Room;
use App\Models\TenantRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TenantRoomResource extends Resource
{
    protected static ?string $model = TenantRoom::class;

    protected static ?string $navigationIcon = 'tenantrooms';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->required(),
                Forms\Components\Select::make('house_id')
                    ->required()
                    ->options(House::all()->pluck('name', 'id'))
                    ->reactive(),

                Forms\Components\Select::make('room_id')
                    ->label('Room')
                    ->required()
                    ->options(function (callable $get) {
                        $houseId = $get('house_id');

                        if (!$houseId) {
                            return [];
                        }

                        return Room::where('house_id', $houseId)
                            ->pluck('room_number', 'id')
                            ->toArray();
                    })
                    ->reactive()
                    ->placeholder('Select a house first to load rooms.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('ID'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label(__('Tenant'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('room.house.name')
                    ->label(__('House')),
                Tables\Columns\TextColumn::make('room.room_number')
                    ->label(__('Room Number'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('room.rent_amount')
                    ->label(__('Rent Amount')),
                Tables\Columns\IconColumn::make('room.is_flat')
                    ->label(__('Is Flat'))
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__("Remove"))
                        ->tooltip(__("Remove Tenants from Room")),
                ]),
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
            'index' => Pages\ListTenantRooms::route('/'),
            'create' => Pages\CreateTenantRoom::route('/create'),
            'edit' => Pages\EditTenantRoom::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use JeffersonGoncalves\Filament\QrCodeField\Forms\Components\QrCodeInput;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('icon')
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->live()
                    ->reactive()
                    ->options([
                        "Bank" => "Bank",
                        "Wallet" => "Wallet",
                        "Cash" => "Cash",
                    ]),
                Forms\Components\Toggle::make('use_qr_code_only')
                    ->label(__('Use Qr Code Only?'))
                    ->visible(fn(Get $get) => in_array($get('type'), ["Bank", "Wallet"]))
                    ->inline()
                    ->live()
                    ->reactive()
                    ->default(true),
                SpatieMediaLibraryFileUpload::make('qr_code')
                    ->label(__('Upload QR Code'))
                    ->imageEditor()
                    ->imageEditorAspectRatios(['1:1'])
                    ->imageCropAspectRatio('1:1')
                    ->visible(fn(Get $get) => $get('use_qr_code_only') && in_array($get('type'), ["Bank", "Wallet"]))
                    ->required(),
                Forms\Components\Toggle::make('status')
                    ->inline(false)
                    ->default(true),
                Section::make('Account Details')
                    ->hidden(fn(Get $get) => $get('use_qr_code_only') || !in_array($get('type'), ["Bank", "Wallet"]))
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label(
                                function (Get $get) {
                                    return $get('type') == "Bank" ? __('Bank Name') : __('Wallet Name');
                                }
                            )
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('account_number')
                            ->label(
                                function (Get $get) {
                                    return $get('type') == "Bank" ? __('Account Number') : __('Wallet ID');
                                }
                            )
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('account_name')
                            ->label(
                                function (Get $get) {
                                    return $get('type') == "Bank" ? __('Account Name') : __('Wallet Name');
                                }
                            )
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('url')
                            ->label(__('Bank Url'))
                            ->nullable()
                            ->hidden()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('branch')
                            ->label(__('Branch Name'))
                            ->required()
                            ->nullable()
                            ->maxLength(255),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label(__('ID')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                SpatieMediaLibraryImageColumn::make('qr_code'),
                Tables\Columns\TextColumn::make('icon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean()
                    ->sortable()
                    ->toggledHiddenByDefault(true),
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
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}

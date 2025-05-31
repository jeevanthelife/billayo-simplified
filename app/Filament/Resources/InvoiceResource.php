<?php

namespace App\Filament\Resources;

use App\Enums\BillingTypeEnum;
use App\Enums\InvoiceItemTypeEnum;
use App\Enums\InvoiceStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'invoice-icon';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()
                    ->schema([
                        Section::make('Invoice Details')
                            ->schema([

                                Forms\Components\TextInput::make('invoice_number')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('invoice_date')
                                    ->required()
                                    ->suffix("A.D"),
                                Forms\Components\DatePicker::make('start_date')
                                    ->required()
                                    ->suffix("A.D"),
                                Forms\Components\DatePicker::make('end_date')
                                    ->required()
                                    ->suffix("A.D"),
                                Forms\Components\Select::make('billing_type')
                                    ->options(BillingTypeEnum::class)
                                    ->required()
                                    ->default(BillingTypeEnum::Monthly->value),
                                Forms\Components\Select::make('status')
                                    ->required()
                                    ->options(InvoiceStatusEnum::class)
                                    ->default(InvoiceStatusEnum::Open->value),
                                Forms\Components\Select::make('tenant_id')
                                    ->relationship('tenant', 'name')
                                    ->required(),
                                Forms\Components\Select::make('payment_status')
                                    ->required()
                                    ->options(PaymentStatusEnum::class)
                                    ->default(PaymentStatusEnum::Pending->value),
                                Forms\Components\Textarea::make('remarks')
                                    ->columnSpanFull()
                                    ->required(function (callable $get) {
                                        $items = $get('invoiceItems') ?? [];

                                        foreach ($items as $item) {
                                            if (($item['title'] ?? null) === InvoiceItemTypeEnum::Others->value) {
                                                return true;
                                            }
                                        }

                                        return false;
                                    }),
                            ])
                            ->compact()
                            ->columns(3)
                            ->columnSpan(3),
                        Section::make("Invoice Payment Details")
                            ->schema([
                                Forms\Components\TextInput::make('sub_total')
                                    ->required()
                                    ->prefix('Rs.')
                                    ->numeric()
                                    ->live()
                                    ->reactive()
                                    ->dehydrated()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $set('grand_total', self::calculateGrandTotal($get('sub_total'), $get('advanced_amount'), $get('due_amount')));
                                    }),
                                Forms\Components\TextInput::make('due_amount')
                                    ->required()
                                    ->prefix('Rs.')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->live()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $set('grand_total', self::calculateGrandTotal($get('sub_total'), $get('advanced_amount'), $get('due_amount')));
                                    }),
                                Forms\Components\TextInput::make('advanced_amount')
                                    ->required()
                                    ->prefix('Rs.')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->live()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $set('grand_total', self::calculateGrandTotal($get('sub_total'), $get('advanced_amount'), $get('due_amount')));
                                    }),
                                Forms\Components\TextInput::make('grand_total')
                                    ->required()
                                    ->prefix('Rs.')
                                    ->numeric()
                                    ->disabled()
                                    ->reactive()
                                    ->dehydrated(),
                                Forms\Components\Placeholder::make('currency')
                                    ->inlineLabel()
                                    ->content(__('NPR')),
                            ])->columnSpan(1)
                            ->compact()
                    ])
                    ->columns(4),

                TableRepeater::make('invoiceItems')
                    ->relationship()
                    ->required()
                    ->reorderable()
                    ->addActionLabel(__('Add Invoice Item'))
                    ->columnSpanFull()
                    ->live()
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $items = $get('invoiceItems') ?? [];
                        $sum = collect($items)->sum(fn($item) => (float)($item['amount'] ?? 0));
                        $set('sub_total', $sum);
                        $set('grand_total', self::calculateGrandTotal(
                            $get('sub_total'),
                            $get('advanced_amount'),
                            $get('due_amount')
                        ));
                    })
                    ->schema([
                        Forms\Components\Select::make('title')
                            ->required()
                            ->live()
                            ->reactive()
                            ->options(InvoiceItemTypeEnum::class),
                        Forms\Components\TextInput::make('description')
                            ->label(__("Other Details"))
                            ->visible(function (Get $get) {
                                $items = $get('../../invoiceItems') ?? [];
                                foreach ($items as $item) {
                                    if (($item['title'] ?? null) === \App\Enums\InvoiceItemTypeEnum::Others->value) {
                                        return true;
                                    }
                                }

                                return false;
                            })
                            ->maxLength(50),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->live()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::updateTotals($set, $get);
                            }),
                        Forms\Components\TextInput::make('rate')
                            ->required()
                            ->prefix('Rs.')
                            ->numeric()
                            ->minValue(0)
                            ->live()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::updateTotals($set, $get);
                            }),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->prefix('Rs.')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sub_total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('billing_type')
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
            ]);;
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    protected static function updateTotals(Set $set, Get $get): void
    {
        $set('amount', ((int)$get('quantity') ?? 0) * ((int)$get('rate') ?? 0));

        $items = $get('../../invoiceItems') ?? [];
        $sum = collect($items)->sum(fn($item) => (float)($item['amount'] ?? 0));
        $set('../../sub_total', $sum);

        // 🔁 Recalculate grand_total too
        $set('../../grand_total', self::calculateGrandTotal(
            $get('../../sub_total'),
            $get('../../advanced_amount'),
            $get('../../due_amount')
        ));
    }

    protected static function calculateGrandTotal($subTotal, $advancedAmount, $dueAmount): float
    {
        $subTotal = (float) ($subTotal ?? 0);
        $advance = (float) ($advancedAmount ?? 0);
        $due = (float) ($dueAmount ?? 0);

        return $subTotal + $due - $advance;
    }
}

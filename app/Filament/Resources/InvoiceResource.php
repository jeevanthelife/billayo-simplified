<?php

namespace App\Filament\Resources;

use App\Enums\BillingTypeEnum;
use App\Enums\InvoiceItemTypeEnum;
use App\Enums\InvoiceStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Room;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'invoice-icon';

    protected static ?string $navigationLabel = 'Billings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('room_id')
                    ->label(__('Room'))
                    ->live()
                    ->reactive()
                    ->options(fn(Room $rooms) => $rooms->pluck("room_number", "id"))
                    ->required()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $room = Room::find($get("room_id"));
                        $previousMeterReading = $room->latest_meter_reading;
                        $set("previous_reading", $previousMeterReading);
                        $set("tenant_id", $room->tenant_id);
                    }),
                Forms\Components\Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->required()
                    ->live()
                    ->reactive()
                    ->disabled()
                    ->visible(fn(Get $get) => $get('room_id'))
                    ->dehydrated(),
                Grid::make()
                    ->schema([
                        Section::make('Electricity Reading')
                            ->visible(fn(Get $get) => $get('room_id'))
                            ->schema([
                                Forms\Components\TextInput::make('new_reading')
                                    ->label(__("New Reading"))
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('previous_reading')
                                    ->label(__("Previous Reading"))
                                    ->required()
                                    ->numeric(),
                            ])
                            ->columns(2),
                        Section::make('Invoice Details')
                            ->schema([
                                Forms\Components\TextInput::make('invoice_number')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('invoice_date')
                                    ->required()
                                    ->suffix("A.D")
                                    ->default(Carbon::today()->format('Y-m-d')),
                                Forms\Components\Select::make('billing_type')
                                    ->options(BillingTypeEnum::class)
                                    ->required()
                                    ->live()
                                    ->reactive()
                                    ->default(BillingTypeEnum::Monthly->value),
                                Forms\Components\DatePicker::make('start_date')
                                    ->required()
                                    ->suffix("A.D")
                                    ->default(Carbon::today()->modify('first day of this month')->day(15)->format('Y-m-d')),
                                Forms\Components\DatePicker::make('end_date')
                                    ->required()
                                    ->suffix("A.D")
                                    ->default(Carbon::today()->modify('first day of next month')->day(15)->format('Y-m-d')),
                                Forms\Components\Select::make('status')
                                    ->required()
                                    ->options(InvoiceStatusEnum::class)
                                    ->default(InvoiceStatusEnum::Open->value),
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
                    ->visible(fn(Get $get) => $get('room_id'))
                    ->addActionLabel(__('Add Invoice Item'))
                    ->columnSpanFull()
                    ->defaultItems(4)
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
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $room = Room::find($get('../../room_id'));
                                $tenant = $room->tenant;

                                if ($get('title') === InvoiceItemTypeEnum::Rent->value) {
                                    $set('quantity', 1);
                                    $set('rate', $room->rent_amount);
                                }

                                if ($get('title') === InvoiceItemTypeEnum::Water->value) {
                                    $set('quantity', 1);
                                    $set('rate', 100);
                                }

                                if ($get('title') === InvoiceItemTypeEnum::Waste->value) {
                                    $set('quantity', 1);
                                    $set('rate', 50);
                                }

                                if ($get('title') == InvoiceItemTypeEnum::Electricity->value) {
                                    $rate = $tenant->electricity_rate;
                                    $set('rate', $rate);
                                    if ($get('../../new_reading') && $get('../../previous_reading')) {
                                        $unit = $get('../../new_reading') - $get('../../previous_reading');
                                        $set('quantity', $unit);
                                    }
                                }
                                self::updateTotals($set, $get);
                            })
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
                // Tables\Columns\TextColumn::make('id')
                //     ->label('ID')
                //     ->sortable(),
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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('grand_total')
                    ->numeric()
                    ->sortable()
                    ->prefix(__("Rs. ")),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('billing_type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->status == InvoiceStatusEnum::Open->value),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('Approve')
                    ->label(__("Approve"))
                    ->color('success')
                    ->icon('approval-icon')
                    ->hidden(fn(Invoice $record) => $record->status == InvoiceStatusEnum::Approved->value)
                    ->form([
                        Forms\Components\Select::make('payment_methods')
                            ->label('Payment Details')
                            ->multiple()
                            ->options(PaymentMethod::get()->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->action(fn(Invoice $record) => $record->update(['status' => InvoiceStatusEnum::Approved->value])),
                ActionGroup::make([
                    Tables\Actions\Action::make('Print')
                        ->label(__("Print"))
                        ->color('info')
                        ->icon('heroicon-o-printer')
                        ->visible(fn(Invoice $record) => $record->status == InvoiceStatusEnum::Approved->value),
                    Tables\Actions\Action::make('Cancel')
                        ->label(__("Cancel"))
                        ->color('danger')
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->visible(fn(Invoice $record) => $record->status == InvoiceStatusEnum::Approved->value)
                        ->action(fn(Invoice $record) => $record->update(['status' => InvoiceStatusEnum::Canceled->value])),
                    // Tables\Actions\Action::make('Open')
                    //     ->label(__("Open"))
                    //     ->color('warning')
                    //     ->icon('heroicon-o-lock-open')
                    //     ->visible(fn(Invoice $record) => $record->status !== InvoiceStatusEnum::Open->value)
                    //     ->action(fn(Invoice $record) => $record->update(['status' => InvoiceStatusEnum::Open->value])),
                    Tables\Actions\ForceDeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn(Model $record) => in_array(
                    $record->status,
                    [InvoiceStatusEnum::Open->value, InvoiceStatusEnum::Canceled->value]
                )
            );
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

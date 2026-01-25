<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'room_id',
        'new_reading',
        'previous_reading',
        'tenant_id',
        'start_date',
        'end_date',
        'sub_total',
        'due_amount',
        'advance_amount',
        'grand_total',
        'status',
        'payment_status',
        'billing_type',
        'remarks',
        'payment_methods',
    ];

    protected function casts(): array
    {
        return [
            'payment_methods' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoicePaymentOptions(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class);
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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
        'month',
        'remarks',
        'payment_methods',
    ];

    protected function casts(): array
    {
        return [
            'payment_methods' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (! $invoice->invoice_number) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    protected static function generateInvoiceNumber(): string
    {
        $now = Carbon::now();
        $prefix = 'INV-' . $now->format('Ym'); // YYYYMM

        // Find last invoice for this year-month
        $last = static::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($last) {
            $lastSeq = (int) Str::afterLast($last->invoice_number, '-');
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        $seq = str_pad($nextSeq, 4, '0', STR_PAD_LEFT); // XXXX

        return $prefix . '-' . $seq; // INV-YYYYMM-XXXX
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

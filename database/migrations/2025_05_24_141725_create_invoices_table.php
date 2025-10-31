<?php

use App\Enums\BillingTypeEnum;
use App\Enums\InvoiceStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Invoice;
use Faker\Provider\ar_EG\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->foreignId('room_id')->constrained('rooms');
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('sub_total', 10, 2)->nullable();
            $table->decimal('due_amount', 10, 2)->nullable();
            $table->decimal('advance_amount', 10, 2)->nullable();
            $table->decimal('grand_total', 10, 2);
            $table->string('status')->default(InvoiceStatusEnum::Open->value);
            $table->string('payment_status')->default(PaymentStatusEnum::Pending->value);
            $table->string('billing_type')->default(BillingTypeEnum::Monthly->value)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

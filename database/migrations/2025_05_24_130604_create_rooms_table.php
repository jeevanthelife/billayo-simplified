<?php

use App\Enums\RoomStatusEnum;
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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained('houses');
            $table->string('room_number');
            $table->decimal('rent_amount', 10, 2);
            $table->boolean('is_flat')->default(false);
            $table->string('status')->default(RoomStatusEnum::Available->value);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

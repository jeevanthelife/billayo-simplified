<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rooms';

    protected $fillable = [
        'house_id',
        'room_number',
        'rent_amount',
        'is_flat',
        'status',
    ];

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Tenant::class,
            table: "tenant_rooms",
            foreignPivotKey: "room_id",
            relatedPivotKey: "tenant_id",
        );
    }
}

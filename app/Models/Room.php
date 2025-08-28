<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rooms';

    protected $fillable = [
        'house_id',
        'room_number',
        'meter_number',
        'latest_meter_reading',
        'rent_amount',
        'tenant_id',
        'is_flat',
        'status',
    ];

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }
}

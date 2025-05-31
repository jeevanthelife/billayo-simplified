<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'contact_email',
        'phone',
        'start_date',
        'status',
    ];

    public function tenantUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_users', 'tenant_id', 'user_id');
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Room::class,
            table: "tenant_rooms",
            foreignPivotKey: "tenant_id",
            relatedPivotKey: "room_id",
        );
    }
}

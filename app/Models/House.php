<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class House extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'houses';

    protected $fillable = [
        'name',
        'house_number',
        'owner_id',
        'address_line_1',
        'city',
        'state',
        'zip_code',
        'country',
        'status',
    ];
}

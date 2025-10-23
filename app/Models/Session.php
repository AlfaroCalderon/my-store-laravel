<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'country',
        'city',
        'latitude',
        'longitude'
    ];

    /*Get the user and his sessions*/

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }
}

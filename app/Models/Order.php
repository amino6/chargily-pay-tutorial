<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'checkout_id',
        'amount',
        'status',
        'paid_at',
    ];
}

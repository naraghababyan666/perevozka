<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;

    protected $fillable = [
        'tariff_name_1',
        'tariff_price_1',
        'tariff_name_2',
        'tariff_price_2',
        'tariff_name_3',
        'tariff_price_3',
        'free_subscription',
        'free_subscription_until'
    ];
}

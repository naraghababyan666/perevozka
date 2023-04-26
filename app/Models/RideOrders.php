<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideOrders extends Model
{
    use HasFactory;


    protected $fillable = ['company_id', 'upload_loc_id', 'onload_loc_id', 'kuzov_type', 'loading_type',
        'max_weight', 'max_volume', 'payment_type', 'ruble_per_kg', 'phone_number', 'company_name', 'is_disabled'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideOrders extends Model
{
    use HasFactory;


    protected $fillable = ['company_id', 'upload_loc_id', 'onload_loc_id', 'kuzov_type', 'loading_type', 'order_title','start_date','end_date','payment_nds',
        'max_weight', 'max_volume', 'payment_type', 'ruble_per_tonn', 'phone_number', 'company_name', 'is_disabled', 'description', 'prepaid', 'manager_id',
        'material_type', 'material_info'];
}

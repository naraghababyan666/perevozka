<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsOrders extends Model
{
    use HasFactory;
    protected $fillable = ['company_id ', 'upload_loc_id', 'onload_loc_id','onload_loc_address','order_title', 'distance', 'kuzov_type',
        'loading_type', 'start_date', 'end_date', 'max_volume', 'payment_type', 'payment_nds',
        'ruble_per_tonn', 'company_name', 'is_disabled', 'description', 'prepaid', 'manager_id'];

    protected $appends = ['upload_city_name', 'onload_city_name'];
    public function getUploadCityNameAttribute($id){
        $city = RussiaRegions::query()->find($id);
        if ($city) {
            return $city->CityName;
        } else {
            return null;
        }
    }
    public function getOnloadCityNameAttribute($id){
        $city = RussiaRegions::query()->find($id);
        if ($city) {
            return $city->CityName;
        } else {
            return null;
        }
    }

}

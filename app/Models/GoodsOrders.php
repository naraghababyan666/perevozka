<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsOrders extends Model
{
    use HasFactory;
    protected $fillable = [];

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RussiaRegions extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['Id2', 'CityId','RegionId','CountryId','FullName','CitySize','Longitude','Latitude','CityName','CityNameEng'];

//    protected $appends = ['upload_city_name', 'onload_city_name'];

    public function getUploadCityNameAttribute($id){
        $city = self::find($id);
        if ($city) {
            return $city->CityName;
        } else {
            return null;
        }
    }
    public function getOnloadCityNameAttribute($id){
        $city = self::find($id);
        if ($city) {
            return $city->CityName;
        } else {
            return null;
        }
    }

    public function getCityNameById($id)
    {
        $city = self::find($id);
        if ($city) {
            return $city->CityName;
        } else {
            return null;
        }
    }

}

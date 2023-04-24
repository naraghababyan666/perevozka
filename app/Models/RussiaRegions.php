<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RussiaRegions extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['Id2', 'CityId','RegionId','CountryId','FullName','CitySize','Longitude','Latitude','CityName','CityNameEng'];
}

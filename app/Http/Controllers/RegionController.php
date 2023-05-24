<?php

namespace App\Http\Controllers;

use App\Models\RussiaRegions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
{
    public function filterCity($text){
        if (strlen($text) < 4){
            return response()->json(['success' => false, 'message' => 'Minimum string length is 2'], 403);
        }
        $result = RussiaRegions::query()->where('CityName', 'like',  $text . '%')->orderBy('CitySize', 'DESC')->get();
        return response()->json(['success' => true, 'cities' => $result]);
    }

    public function getInfoCityById($id){
        $city = RussiaRegions::query()->where('CityId', '=', $id)->first();
        if(!is_null($city)){
            return response()->json(['success' => true, 'data' => $city], 200);
        }
        return response()->json(['success' => false, 'data' => 'City not found'], 404);
    }
}

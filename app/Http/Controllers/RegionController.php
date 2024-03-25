<?php

namespace App\Http\Controllers;

use App\Models\RussiaOnlyRegions;
use App\Models\RussiaRegions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
{
    public function filterCity(Request $request){
        $text = $request['cityName'];
        dd($text, strlen($text));
        if (strlen($text) < 4){
            return response()->json(['success' => false, 'message' => 'Minimum string length is 2'], 403);
        }
        $result = RussiaRegions::query()->where('FullName', 'like',  $text . '%')->orderBy('CitySize', 'DESC')->get();
        return response()->json(['success' => true, 'cities' => $result]);
    }
    public function filterRegion($text){
        if (strlen($text) < 4){
            return response()->json(['success' => false, 'message' => 'Minimum string length is 2'], 403);
        }
        $result = RussiaOnlyRegions::query()->where('Name', 'like',  $text . '%')->get();
        return response()->json(['success' => true, 'regions' => $result]);
    }

    public function getInfoCityById($id){
        $city = RussiaRegions::query()->where('CityId', '=', $id)->first();
        if(!is_null($city)){
            return response()->json(['success' => true, 'data' => $city], 200);
        }
        return response()->json(['success' => false, 'data' => 'City not found'], 404);
    }
}

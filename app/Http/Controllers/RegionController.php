<?php

namespace App\Http\Controllers;

use App\Models\RussiaRegions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
{
    public function filterCity($text){
        if (strlen($text) < 2){
            return response()->json(['success' => false, 'message' => 'Minimum string length is 2']);
        }
        $result = RussiaRegions::query()->where('CityName', 'like', '%' . $text . '%')->orderBy('CitySize', 'DESC')->get();
//        $sql = "SELECT * FROM `russia_regions` WHERE `CityName` LIKE '%Москва%'";
//        $result = DB::select($sql);
        return response()->json(['success' => true, 'cities' => $result]);
    }
}

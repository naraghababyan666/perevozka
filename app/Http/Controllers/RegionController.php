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
        $result = RussiaRegions::query()->where('CityName', 'like', '%' . $text . '%')->orderBy('CitySize', 'DESC')->get();
        return response()->json(['success' => true, 'cities' => $result]);
    }
}

<?php

namespace App\Http\Controllers;


use App\Models\Company;
use App\Models\GoodsOrders;
use App\Models\Region;
use App\Models\RideOrders;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function companyById($id){
        $company = Company::query()->where('id', $id)->select('phone_number', 'company_name', 'inn', 'ogrn', 'legal_address', 'postal_address', 'logo_url')->first();
        return response()->json(['success' => true, 'company' => $company]);
    }

    public function companyByName($company){
        dd($company);
    }

    public function createRide(Request $request){
        if(Auth::user()['role_id'] == Company::IS_OWNER_AND_DRIVER || Auth::user()['role_id'] == Company::IS_DRIVER){
            $validator = Validator::make($request->all(), [
                'company_id' => 'required|exists:companies',
                'upload_loc_id' => 'required',
                'onload_loc_id' => 'required',
                'kuzov_type' => 'required',
                'loading_type' => 'required',
                'max_weight' => 'required',
                'max_volume' => 'required',
                'payment_type' => 'required',
                'ruble_per_kg' => 'required',
                'phone_number' => 'required',
                'company_name' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    "errors" => $validator->errors()
                ])->header('Status-Code', 200);
            }
            RideOrders::query()->create($validator->validated());

        }
    }

    public function getOrders(\Illuminate\Http\Request $request){
        // Координаты города Анапы
//        $lat1 = 44.8949650;
//        $lng1 = 37.3161700;
        if(isset($request->all()['lat']) && isset($request->all()['lng'])) {
            $latitude = $request->all()['lat'];
            $longitude = $request->all()['lng'];
            // Радиус поиска в км

            $radius = (int)$request->all()['radius'] ?? 100;
            $radius = 1 * $radius;
            // Функция для вычисления расстояния между двумя точками на поверхности Земли
            function distance($lat2, $lng2, $lat_start, $lng_start)
            {
                //            $lat1 = 44.8949650;
                //            $lng1 = 37.3161700;
                $R = 6371;  // Радиус Земли в км
                $dlat = deg2rad($lat2 - $lat_start);
                $dlng = deg2rad($lng2 - $lat_start);
                $a = sin($dlat / 2) ** 2 + cos(deg2rad($lat_start)) * cos(deg2rad($lat2)) * sin($dlng / 2) ** 2;
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $d = $R * $c;
                return $d;
            }


            $cities = Region::all();

            // Отфильтрованный массив городов
            $filtered_cities = array();
            foreach ($cities as $city) {
                $d = distance($city['lat'], $city['lng'], $latitude, $longitude);
                if ($d <= $radius) {
                    $city['distance'] = $d;
                    $filtered_cities[] = $city;
                }
            }
            dd($filtered_cities);
        }else{
            $orders = GoodsOrders::all();
            return response()->json(['success' => true, 'orders' => $orders]);
        }
    }

}

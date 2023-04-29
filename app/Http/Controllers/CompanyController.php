<?php

namespace App\Http\Controllers;


use App\Models\Company;
use App\Models\GoodsOrders;
use App\Models\Region;
use App\Models\RideOrders;
use App\Models\RussiaRegions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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

    public function createRide(\Illuminate\Http\Request $request){
        if(Auth::user()['role_id'] == Company::IS_OWNER_AND_DRIVER || Auth::user()['role_id'] == Company::IS_DRIVER){
            $validator = Validator::make($request->all(), [
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
            $data = $validator->validated();
            $data['company_id'] = Auth::id();
            RideOrders::query()->create($data);
            $lastRide = RideOrders::query()->orderBy('created_at', "DESC")->first();
            return response()->json(['success' => true, 'data' => $lastRide] );
        }else{
            return response()->json(['success' => false, 'message' => 'Permission denied!']);
        }
    }

    public function getMyOrders(){
        $orders = GoodsOrders::query()->where('company_id', Auth::id())->get();
        foreach ($orders as $order){
            $order['upload_city_name'] = ((new \App\Models\RussiaRegions)->getCityNameById($order['upload_loc_id']));
            $order['onload_city_name'] = ((new \App\Models\RussiaRegions)->getCityNameById($order['onload_loc_id']));
        }
        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function getOrders(\Illuminate\Http\Request $request){
        $data= $request->all();
//        dd(isset($data['upload_loc_radius']));
        if (!isset($data['upload_loc_radius'])){
            $data['upload_loc_radius'] = 100;
        }
        if(isset($data['upload_loc_id'] )){
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.ati.su/v1.0/dictionaries/cities/". $data['upload_loc_id'] ."/near?radius=".$data['upload_loc_radius'],// your preferred link
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_TIMEOUT => 30000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    // Set Here Your Requesred Headers
                    'Content-Type: application/json',
                    'Authorization: Bearer 3686751bb23c4aed92e18fd096f5b18e'
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                return response()->json(['success' => false, 'message' => 'Server error']);
            } else {
                dd(json_decode($response));
            }



//            $response = Http::get("https://api.ati.su/v1.0/dictionaries/cities/". $data['upload_loc_id'] ."/near?radius=".$data['upload_loc_radius']);
//            $response = Http::get("https://dummyjson.com/products/1");

//            dd($data['upload_loc_id'], $data['upload_loc_radius'], $response->body());
        }





        // Координаты города Анапы
//        $lat1 = 44.8949650;
//        $lng1 = 37.3161700;
//        if(isset($request->all()['lat']) && isset($request->all()['lng'])) {
//            $latitude = $request->all()['lat'];
//            $longitude = $request->all()['lng'];
//            // Радиус поиска в км
//
//            $radius = (int)$request->all()['radius'] ?? 100;
//            $radius = 1 * $radius;
//            // Функция для вычисления расстояния между двумя точками на поверхности Земли
//            function distance($lat2, $lng2, $lat_start, $lng_start)
//            {
//                //            $lat1 = 44.8949650;
//                //            $lng1 = 37.3161700;
//                $R = 6371;  // Радиус Земли в км
//                $dlat = deg2rad($lat2 - $lat_start);
//                $dlng = deg2rad($lng2 - $lat_start);
//                $a = sin($dlat / 2) ** 2 + cos(deg2rad($lat_start)) * cos(deg2rad($lat2)) * sin($dlng / 2) ** 2;
//                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
//                $d = $R * $c;
//                return $d;
//            }
//
//
//            $cities = Region::all();
//
//            // Отфильтрованный массив городов
//            $filtered_cities = array();
//            foreach ($cities as $city) {
//                $d = distance($city['lat'], $city['lng'], $latitude, $longitude);
//                if ($d <= $radius) {
//                    $city['distance'] = $d;
//                    $filtered_cities[] = $city;
//                }
//            }
//            dd($filtered_cities);
//        }else{
//            $orders = GoodsOrders::all();
//            return response()->json(['success' => true, 'orders' => $orders]);
//        }
    }

}

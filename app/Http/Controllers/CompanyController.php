<?php

namespace App\Http\Controllers;


use App\Http\Middleware\CheckSubscriptionMiddleware;
use App\Models\Company;
use App\Models\GoodsOrders;
use App\Models\Region;
use App\Models\Review;
use App\Models\RideOrders;
use App\Models\RussiaRegions;
use App\Models\Subscriptions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function companyById($id){
        $company = Company::query()
            ->where('id', $id);
        $user =  auth('sanctum')->user();
        if (!is_null($user)) {
            $data = Subscriptions::query()->where('company_id', $user['id'])->where('valid_until', '>', Carbon::now())->first();
            if (!is_null($data)) {
                $company->select('phone_number', 'company_name', 'inn', 'ogrn', 'legal_address', 'postal_address', 'logo_url')->first();
            } else {
                $company->select('company_name', 'inn', 'ogrn', 'legal_address', 'postal_address', 'logo_url')->first();
            }
        }else{
            $company->select('company_name', 'inn', 'ogrn', 'legal_address', 'postal_address', 'logo_url')->first();
        }
        $company = $company->first();
        return response()->json(['success' => true, 'company' => $company]);

    }

//    public function companyByName($id){
//        $company = Company::query()->findOrFail($id);
//        return response()->json(['success' => true, 'company' => $company]);
//    }

    public function companyReviews($id){
//        $reviews = Review::query()->where('company_id', $id)->where('is_published', 1)->with(['writer' => function ($query) {
//            $query->select('id', 'email', 'phone_number', 'company_name', 'legal_address');
//        }])->get();
        $reviewsCount = Review::query()->where('company_id', $id)->where('is_published', Review::CONFIRMED)->count();
        return response()->json(['success' => true, 'reviews_count' => $reviewsCount]);
    }

    public function companyList(\Illuminate\Http\Request $request){
        $data = $request->all();
        $companies = [];
        if(Auth::user()['role_id'] == Company::IS_OWNER){
            $sql =  "SELECT c.id,  IF(${data['is_subscribed']} = 1, c.phone_number, NULL) AS phone_number, c.email, c.role_id, c.company_name,
                c.inn, c.ogrn, c.legal_address, c.postal_address, c.logo_url from `companies` as c
                WHERE c.role_id = 2";
            if (!empty($data['searchValue'])) {
                $sql .= " AND c.company_name LIKE '%${data['searchValue']}%'";
            }
        }else if (Auth::user()['role_id'] == Company::IS_DRIVER){
            $sql =  "SELECT c.id,  IF(${data['is_subscribed']} = 1, c.phone_number, NULL) AS phone_number, c.email, c.role_id, c.company_name,
                c.inn, c.ogrn, c.legal_address, c.postal_address, c.logo_url from `companies` as c
                WHERE c.role_id = 1";
            if (!empty($data['searchValue'])) {
                $sql .= " AND c.company_name LIKE '%${data['searchValue']}%'";
            }
        }else{
            $sql =  "SELECT c.id,  IF(${data['is_subscribed']} = 1, c.phone_number, NULL) AS phone_number, c.email, c.role_id, c.company_name,
                c.inn, c.ogrn, c.legal_address, c.postal_address, c.logo_url from `companies` as c";
            if (!empty($data['searchValue'])) {
                $sql .= " WHERE c.company_name LIKE '%${data['searchValue']}%'";
            }
        }
        $data = DB::select($sql);
        return response()->json(['data' => $data]);
    }

    public function createRide(\Illuminate\Http\Request $request){
        if(Auth::user()['role_id'] == Company::IS_OWNER_AND_DRIVER || Auth::user()['role_id'] == Company::IS_DRIVER){
            $validator = Validator::make($request->all(), [
                'upload_loc_id' => 'required',
                'onload_loc_id' => 'required',
                'order_title' => 'required',
                'kuzov_type' => 'required',
                'loading_type' => 'required',
                'max_weight' => 'required',
                'max_volume' => 'required',
                'payment_type' => 'required',
                'payment_nds' => 'required',
                'prepaid' => 'required',
                'ruble_per_kg' => 'required',
                'company_name' => 'required',
                'manager_id' => 'required',
                'material_type' => 'required',
                'material_info' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    "errors" => $validator->errors()
                ])->header('Status-Code', 200);
            }
            $data = $validator->validated();
            $data['company_id'] = Auth::id();
            $data['description'] = $request->all()['description'] ?? null;
            RideOrders::query()->create($data);
            $lastRide = RideOrders::query()->orderBy('created_at', "DESC")->first();
            return response()->json(['success' => true, 'data' => $lastRide] );
        }else{
            return response()->json(['success' => false, 'message' => 'Permission denied!']);
        }
    }

    public function getMyOrders(){
        $userID = Auth::id();
        $sql = "SELECT g.id, g.company_id, g.upload_loc_id, g.upload_loc_info, g.onload_loc_id,g.onload_loc_info, g.order_title, g.kuzov_type,
                        g.loading_type, g.start_date, g.end_date, g.max_weight, g.max_volume, g.payment_type, g.payment_nds, g.prepaid, g.ruble_per_kg,
                        g.company_name, g.is_disabled, g.created_at,g.description,g.material_type,g.material_info,
                        upload.CityName AS upload_city_name, onload.CityName AS onload_city_name,
                        managers.phone_number AS manager_phone_number, managers.FullName AS manager_name
                 from `goods_orders` as g
                 JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                 JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                 JOIN managers managers ON g.manager_id = managers.id
                WHERE g.company_id = '${userID}';
                ";
        $orders = DB::select($sql);
        return response()->json(['success' => true, 'data' => $orders]);
    }
    public function getMyRides(){
        $userID = Auth::id();
        $sql = "SELECT g.id, g.company_id, g.upload_loc_id, g.onload_loc_id, g.order_title, g.kuzov_type,
                        g.loading_type, g.max_weight, g.max_volume, g.payment_type, g.payment_nds, g.prepaid, g.ruble_per_kg,
                        g.company_name, g.is_disabled, g.created_at,g.description,g.manager_id,g.material_type,g.material_info,
                        managers.phone_number AS manager_phone_number, managers.FullName AS manager_name,
                        upload.CityName AS upload_city_name, onload.CityName AS onload_city_name from `ride_orders` as g
                     JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                     JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                     JOIN managers managers ON g.manager_id = managers.id WHERE g.company_id = '${userID}'";
        $rides = DB::select($sql);
        return response()->json(['success' => true, 'data' => $rides]);
    }

    public function deleteRide($id){
        RideOrders::query()->findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Race successfully deleted']);
    }

    public function getRides(\Illuminate\Http\Request $request)
    {
        $data= $request->all();
        if (!isset($data['upload_loc_radius'])){
            $data['upload_loc_radius'] = 100;
        }
        if (!isset($data['onload_loc_radius'])){
            $data['onload_loc_radius'] = 100;
        }
        if($data['upload_loc_radius'] > 300 || $data['onload_loc_radius'] > 300){
            return response()->json(['success' => false, 'message' => 'Параметр радиус должен быть больше 0 и меньше 300']);
        }

        $upload_city_ids = [];
        $onload_city_ids = [];
        $where_text =  '';
        $where = [];
        if(isset($data['upload_loc_id'] )) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.ati.su/v1.0/dictionaries/cities/" . $data['upload_loc_id'] . "/near?count=20&radius=" . $data['upload_loc_radius'],// your preferred link
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
                foreach (json_decode($response) as $item){
                    $upload_city_ids[] = (int) $item->CityId;
                }
                $upload_city_ids[] = (int) $data['upload_loc_id'];

                $where[] = "g.upload_loc_id IN (".implode(",", $upload_city_ids).")";
            }
        }
        if(isset($data['onload_loc_id'] )) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.ati.su/v1.0/dictionaries/cities/" . $data['onload_loc_id'] . "/near?count=20&radius=" . $data['onload_loc_radius'],// your preferred link
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_TIMEOUT => 30000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
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
//                dd(json_decode($response));
                foreach (json_decode($response) as $item){
                    $onload_city_ids[] = $item->CityId;
                }
                $onload_city_ids[] = (int) $data['onload_loc_id'];
                $where[] = "g.onload_loc_id IN (".implode(",", $onload_city_ids).")";
            }
        }
        if(isset($data['loading_type'])){
            $where[] = "g.loading_type = '${data['loading_type']}'";
        }
        if(isset($data['min_deposit'])){
            $where[] = "g.ruble_per_kg > '${data['min_deposit']}'";
        }
//        if(isset($data['date_from']) && isset($data['date_to'])){
//            $where[] = "g.start_date >= '${data['date_from']}'";
//            $where[] = "g.end_date <= '${data['date_to']}'";
//        }else if(isset($data['date_from'])){
//            $where[] = "g.start_date >= '${data['date_from']}'";
//        }
        if(isset($data['material_type'])){
            $where[] = "g.material_type LIKE '%${data['material_type']}%'";
        }
        if(isset($data['material_info'])){
            $where[] = "g.material_info LIKE '%${data['material_info']}%'";
        }
        if(!empty($where)){
            $where_text = implode(' AND ', $where);
        }
        if(strlen($where_text) != 0){
//            $sql = "SELECT * from `goods_orders` where ${where_text}";
//            $sql = "SELECT * from `goods_orders` where ${where_text}";
            $sql = "SELECT g.id, g.company_id, g.upload_loc_id, g.onload_loc_id, g.order_title, g.kuzov_type, g.loading_type,
                            g.max_weight, g.max_volume, g.payment_type, g.payment_nds, g.ruble_per_kg, IF(${data['is_subscribed']} = 1, managers.phone_number, NULL) AS manager_phone_number,
                            IF(${data['is_subscribed']} = 1, managers.FullName, NULL) AS manager_name,
                            g.company_name, g.is_disabled, g.created_at, IF(${data['is_subscribed']} = 1, g.description, NULL) AS order_description, g.prepaid, g.manager_id,
                            g.material_type, g.material_info,
                            upload.CityName AS upload_city_name, onload.CityName AS onload_city_name from `ride_orders` as g
                     JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                     JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                    JOIN managers managers ON g.manager_id = managers.id
                    where ${where_text} ;" ;
        }else{
            $sql =  "SELECT g.id, g.company_id, g.upload_loc_id, g.onload_loc_id, g.order_title, g.kuzov_type, g.loading_type,
                            g.max_weight, g.max_volume, g.payment_type, g.payment_nds, g.ruble_per_kg, IF(${data['is_subscribed']} = 1, managers.phone_number, NULL) AS manager_phone_number,
                            IF(${data['is_subscribed']} = 1, managers.FullName, NULL) AS manager_name,
                            g.company_name, g.is_disabled, g.created_at, IF(${data['is_subscribed']} = 1, g.description, NULL) AS order_description, g.prepaid, g.manager_id,
                            g.material_type, g.material_info, upload.CityName AS upload_city_name, onload.CityName AS onload_city_name from `ride_orders` as g
                     JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                     JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                     JOIN managers managers ON g.manager_id = managers.id;";
        }
        $aa = DB::select($sql);

        return response()->json(['success' => true, 'orders' => $aa]);
    }

    public function getOrders(\Illuminate\Http\Request $request){
        //upload_loc_id
        //upload_loc_radius
        //onload_loc_id
        //onload_loc_radius
        //loading_type
        //date_from
        //date_to
        //min_deposit
        //order_by norery
        $data= $request->all();

        if (!isset($data['upload_loc_radius'])){
            $data['upload_loc_radius'] = 100;
        }
        if (!isset($data['onload_loc_radius'])){
            $data['onload_loc_radius'] = 100;
        }
        if($data['upload_loc_radius'] > 300 || $data['onload_loc_radius'] > 300){
            return response()->json(['success' => false, 'message' => 'Параметр радиус должен быть больше 0 и меньше 300']);
        }

        $upload_city_ids = [];
        $onload_city_ids = [];
        $where_text =  '';
        $where = [];
        if(isset($data['upload_loc_id'] )) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.ati.su/v1.0/dictionaries/cities/" . $data['upload_loc_id'] . "/near?count=20&radius=" . $data['upload_loc_radius'],// your preferred link
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
                foreach (json_decode($response) as $item){
                    $upload_city_ids[] = (int) $item->CityId;
                }
                $upload_city_ids[] = (int) $data['upload_loc_id'];

                $where[] = "g.upload_loc_id IN (".implode(",", $upload_city_ids).")";
            }
        }
        if(isset($data['onload_loc_id'] )) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.ati.su/v1.0/dictionaries/cities/" . $data['onload_loc_id'] . "/near?count=20&radius=" . $data['onload_loc_radius'],// your preferred link
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_TIMEOUT => 30000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
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
//                dd(json_decode($response));
                foreach (json_decode($response) as $item){
                    $onload_city_ids[] = $item->CityId;
                }
                $onload_city_ids[] = (int) $data['onload_loc_id'];
                $where[] = "g.onload_loc_id IN (".implode(",", $onload_city_ids).")";
            }
        }
        if(isset($data['loading_type'])){
            $where[] = "g.loading_type = '${data['loading_type']}'";
        }
        if(isset($data['min_deposit'])){
            $where[] = "g.ruble_per_kg > '${data['min_deposit']}'";
        }
        if(isset($data['date_from']) && isset($data['date_to'])){
            $where[] = "g.start_date >= '${data['date_from']}'";
            $where[] = "g.end_date <= '${data['date_to']}'";
        }else if(isset($data['date_from'])){
            $where[] = "g.start_date >= '${data['date_from']}'";
        }
        if(isset($data['material_type'])){
            $where[] = "g.material_type LIKE '%${data['material_type']}%'";
        }
        if(isset($data['material_info'])){
            $where[] = "g.material_info LIKE '%${data['material_info']}%'";
        }
        if(!empty($where)){
            $where_text = implode(' AND ', $where);
        }
        if(strlen($where_text) != 0){
//            $sql = "SELECT * from `goods_orders` where ${where_text}";
//            $sql = "SELECT * from `goods_orders` where ${where_text}";
            $sql = "SELECT g.id, g.company_id, g.upload_loc_id, g.upload_loc_info, g.onload_loc_id, g.onload_loc_info, g.kuzov_type, g.loading_type, g.start_date, g.end_date, g.max_weight,
                            g.max_volume, g.payment_type, g.payment_nds, g.ruble_per_kg,IF(${data['is_subscribed']} = 1, managers.phone_number, NULL) AS manager_phone_number,
                            IF(${data['is_subscribed']} = 1, managers.FullName, NULL) AS manager_name,
                            g.company_name, g.is_disabled, g.created_at, IF(${data['is_subscribed']} = 1, g.description, NULL) AS order_description, g.prepaid, g.manager_id,
                            g.material_type, g.material_info,
                            upload.CityName AS upload_city_name, onload.CityName AS onload_city_name from `goods_orders` as g
                     JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                     JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                     JOIN managers managers ON g.manager_id = managers.id
                    where ${where_text}
";
        }else{
            $sql =  "SELECT g.id, g.company_id, g.upload_loc_id, g.upload_loc_info, g.onload_loc_id, g.onload_loc_info, g.kuzov_type, g.loading_type, g.start_date, g.end_date, g.max_weight,
                            g.max_volume, g.payment_type, g.payment_nds, g.ruble_per_kg,IF(${data['is_subscribed']} = 1, managers.phone_number, NULL) AS manager_phone_number,
                            IF(${data['is_subscribed']} = 1, managers.FullName, NULL) AS manager_name,
                            g.company_name, g.is_disabled, g.created_at, IF(${data['is_subscribed']} = 1, g.description, NULL) AS order_description, g.prepaid, g.manager_id,
                            g.material_type, g.material_info,
                            upload.CityName AS upload_city_name, onload.CityName AS onload_city_name from `goods_orders` as g
                     JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                     JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                     JOIN managers managers ON g.manager_id = managers.id;
";
        }
        $aa = DB::select($sql);
        return response()->json(['success' => true, 'orders' => $aa]);
        dd($aa, $onload_city_ids, $sql);


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

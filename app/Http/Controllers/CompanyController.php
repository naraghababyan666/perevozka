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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use YooKassa\Client;

class CompanyController extends Controller
{
    public function changeIsPaymentWorking(\Illuminate\Http\Request $request){
        $value = $request->all()['value'];
//        $user = Company::query()->where('id', Auth::id())->first();
//        $user->isPaymentWorking = $value;
//        $user->save();
        Company::query()->update(['isPaymentWorking' => $value]);
        return response()->json(['success' => true]);
    }

    public function updateProfile(\Illuminate\Http\Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'phone_number' => 'required',
            'company_name' => 'required',
            'inn' => 'required',
            'ogrn' => 'required',
            'legal_address' => 'required',
            'postal_address' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ])->header('Status-Code', 200);
        }
        $id = Auth::id();
        Company::query()->where('id', $id)->update($validator->validated());
        return response()->json(['success' => true, 'message' => 'User data successfully updated']);
    }

    public function companyById($id){
        $company = Company::query()
            ->where('id', $id);
        $user =  auth('sanctum')->user();
        if (!is_null($user)) {
            $data = Subscriptions::query()->where('company_id', $user['id'])->where('valid_until', '>', Carbon::now())->first();
            if (!is_null($data)) {
                $company->select('id', 'phone_number', 'company_name', 'inn', 'ogrn', 'legal_address', 'postal_address', 'logo_url')->with('manager')->first();
            } else {
                $company->select('id', 'company_name', 'inn',  'legal_address', 'postal_address', 'logo_url')->first();
            }
        }else{
            $company->select('id', 'company_name', 'inn', 'legal_address', 'postal_address', 'logo_url')->first();
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

    public function changeSubscribeMessageStatus(){
        $company = Company::query()->where('id', Auth::id())->update(['subscribe_message_status' => '1']);
        return response()->json(['success' => true]);
    }

    public function companyList(\Illuminate\Http\Request $request){
        $data = $request->all();
        $companies = [];
        $company = Company::query();
        $limit = $request->all()['limit'] ?? 5;
        if(Auth::user()['role_id'] == Company::IS_OWNER){
//            $sql =  "SELECT c.id,  c.email, c.role_id, c.company_name,
//                            IF(${data['is_subscribed']} = 1, managers.phone_number, NULL) AS manager_phone_number,
//                            IF(${data['is_subscribed']} = 1, managers.FullName, NULL) AS manager_name,
//                            c.inn, c.ogrn, c.legal_address, c.postal_address, c.logo_url from `companies` as c
//                    JOIN managers managers ON managers.company_id = c.id
//                    WHERE c.role_id = 2";
//            if (!empty($data['searchValue'])) {
//                $sql .= " AND c.company_name LIKE '%${data['searchValue']}%'";
//            }
            $company->where('role_id', Company::IS_DRIVER);
            if (!empty($data['searchValue'])) {
                $company->where('company_name', 'LIKE', "%{$data['searchValue']}%");
            }
            if (!empty($data['company_id'])) {
                $company->where('id', '=', $data['company_id']);
            }
            if (!empty($data['inn'])) {
                $company->where('inn', '=', $data['inn']);
            }
            if($data['is_subscribed'] == 1){
                $company->with('manager');
            }
            $company = $company->paginate($limit);

            return response()->json(['success' => true, 'data' => $company]);
        }
        else if (Auth::user()['role_id'] == Company::IS_DRIVER){
//            $sql =  "SELECT c.id,
//                            IF(${data['is_subscribed']} = 1, managers.phone_number, NULL) AS manager_phone_number,
//                            IF(${data['is_subscribed']} = 1, managers.FullName, NULL) AS manager_name, c.email, c.role_id, c.company_name,
//                c.inn, c.ogrn, c.legal_address, c.postal_address, c.logo_url from `companies` as c
//                    JOIN managers managers ON c.id = managers.company_id
//                WHERE c.role_id = 1";
//            if (!empty($data['searchValue'])) {
//                $sql .= " AND c.company_name LIKE '%${data['searchValue']}%'";
//            }
            $company->where('role_id', Company::IS_OWNER);
            if (!empty($data['searchValue'])) {
                $company->where('company_name', 'LIKE', "%{$data['searchValue']}%");
            }
            if (!empty($data['company_id'])) {
                $company->where('id', '=', $data['company_id']);
            }
            if (!empty($data['inn'])) {
                $company->where('inn', '=', $data['inn']);
            }
            if($data['is_subscribed'] == 1){
                $company->with('manager');
            }
            $company = $company->paginate($limit);

            return response()->json(['success' => true, 'data' => $company]);
        }else if(Auth::user()['role_id'] == Company::IS_OWNER_AND_DRIVER){
//            $sql =  "SELECT c.id,
//                            IF(${data['is_subscribed']} = 1, managers.phone_number, NULL) AS manager_phone_number,
//                            IF(${data['is_subscribed']} = 1, managers.FullName, NULL) AS manager_name, c.email, c.role_id, c.company_name,
//                c.inn, c.ogrn, c.legal_address, c.postal_address, c.logo_url from `companies` as c
//                    JOIN managers managers ON c.id = managers.company_id";
//            if (!empty($data['searchValue'])) {
//                $sql .= " WHERE c.company_name LIKE '%${data['searchValue']}%'";
//            }
            if (!empty($data['searchValue'])) {
                $company->where('company_name', 'LIKE', "%{$data['searchValue']}%");
            }
            if (!empty($data['company_id'])) {
                $company->where('id', '=', $data['company_id']);
            }
            if (!empty($data['inn'])) {
                $company->where('inn', '=', $data['inn']);
            }
            if($data['is_subscribed'] == 1){
                $company->with('manager');
            }
            $company = $company->paginate($limit);

            return response()->json(['success' => true, 'data' => $company]);
        }else{
            return response()->json(['success' => false, 'message' => 'Server error'], 500);

        }
    }

    public function updateRide(\Illuminate\Http\Request $request, $id){
        $validator = Validator::make($request->all(), [
            'upload_loc_id' => 'required',
            'onload_loc_id' => 'required',
            'kuzov_type' => 'required',
            'max_volume' => 'required',
            'description' => 'required',
            'company_name' => 'required',
            'manager_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ])->header('Status-Code', 200);
        }
        $ride = RideOrders::query()->findOrFail($id);
        $ride->update($request->all());
//        $data =  DB::table('goods_orders')->latest()->first();

        return response()->json(['success' => true, 'message' => 'Your order successfully updated']);
    }

    public function createRide(\Illuminate\Http\Request $request){
        if(Auth::user()['role_id'] == Company::IS_OWNER_AND_DRIVER || Auth::user()['role_id'] == Company::IS_DRIVER){
            $validator = Validator::make($request->all(), [
                'upload_loc_id' => 'required',
                'onload_loc_id' => 'required',
                'kuzov_type' => 'required',
                'max_volume' => 'required',
                'company_name' => 'required',
                'manager_id' => 'required',
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
        $sql = "SELECT g.id, g.company_id, g.upload_loc_id, g.onload_loc_id, g.onload_loc_address, g.order_title, g.kuzov_type,
                        g.loading_type, g.start_date, g.end_date, g.max_volume, g.payment_type, g.payment_nds, g.prepaid, g.ruble_per_tonn,
                        g.company_name, g.is_disabled, g.created_at,g.description,g.distance , g.manager_id,managers.phone_number, managers.FullName,
                        upload.CityName AS upload_city_name, onload.CityName AS onload_city_name,
                        managers.phone_number AS manager_phone_number, managers.FullName AS manager_name, managers.id AS manager_id
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
        $sql = "SELECT g.id, g.company_id, g.upload_loc_id, g.onload_loc_id, g.kuzov_type,
                       g.max_volume, g.company_name, g.is_disabled, g.created_at,g.description,g.manager_id,
                        managers.phone_number AS manager_phone_number, managers.FullName AS manager_name,
                        upload.CityName AS upload_city_name, onload.CityName AS onload_city_name
                     from `ride_orders` as g
                     JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                     JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                     JOIN managers managers ON g.manager_id = managers.id WHERE g.company_id = '${userID}'";
        $rides = DB::select($sql);
        return response()->json(['success' => true, 'data' => $rides]);
    }

    public function deleteRide($id){
        RideOrders::query()->where('id', $id)->where('company_id', Auth::id())->delete();
        return response()->json(['success' => true, 'message' => 'Race successfully deleted']);
    }

    public function deleteOrder($id){
        GoodsOrders::query()->where('id', $id)->where('company_id', Auth::id())->delete();
        return response()->json(['success' => true, 'message' => 'Race successfully deleted']);
    }

    public function deleteCompany(): \Illuminate\Http\JsonResponse
    {
        $id = Auth::id();
        Company::query()->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Company profile successfully deleted']);
    }
    public function getRides(\Illuminate\Http\Request $request)
    {

        $data= $request->all();

        $offset = $request->all()['offset'] ?? 0;
        $limit =  $request->all()['limit'] ?? 3;

        if (!isset($data['upload_loc_radius'])){
            $data['upload_loc_radius'] = 5;
        }
        if (!isset($data['onload_loc_radius'])){
            $data['onload_loc_radius'] = 5;
        }
        if($data['upload_loc_radius'] > 300 || $data['onload_loc_radius'] > 300){
            return response()->json(['success' => false, 'message' => 'Параметр радиус должен быть больше 0 и меньше 300']);
        }

        $upload_city_ids = [];
        $onload_city_ids = [];
        $where_text =  '';
        $where = [];
//        if(isset($data['upload_loc_id'] )) {
//            $curl = curl_init();
//            curl_setopt_array($curl, array(
//                CURLOPT_URL => "https://api.ati.su/v1.0/dictionaries/cities/" . $data['upload_loc_id'] . "/near?count=20&radius=" . $data['upload_loc_radius'],// your preferred link
//                CURLOPT_RETURNTRANSFER => true,
//                CURLOPT_ENCODING => "",
//                CURLOPT_TIMEOUT => 30000,
//                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                CURLOPT_CUSTOMREQUEST => "GET",
//                CURLOPT_HTTPHEADER => array(
//                    // Set Here Your Requesred Headers
//                    'Content-Type: application/json',
//                    'Authorization: Bearer 3686751bb23c4aed92e18fd096f5b18e'
//                ),
//            ));
//            $response = curl_exec($curl);
//            $err = curl_error($curl);
//            curl_close($curl);
//
//            if ($err) {
//                return response()->json(['success' => false, 'message' => 'Server error']);
//            } else {
//                foreach (json_decode($response) as $item){
//                    $upload_city_ids[] = (int) $item->CityId;
//                }
//                $upload_city_ids[] = (int) $data['upload_loc_id'];
//
//                $where[] = "g.upload_loc_id IN (".implode(",", $upload_city_ids).")";
//            }
//        }
//        if(isset($data['onload_loc_id'] )) {
//            $curl = curl_init();
//            curl_setopt_array($curl, array(
//                CURLOPT_URL => "https://api.ati.su/v1.0/dictionaries/cities/" . $data['onload_loc_id'] . "/near?count=20&radius=" . $data['onload_loc_radius'],// your preferred link
//                CURLOPT_RETURNTRANSFER => true,
//                CURLOPT_ENCODING => "",
//                CURLOPT_TIMEOUT => 30000,
//                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                CURLOPT_CUSTOMREQUEST => "GET",
//                CURLOPT_HTTPHEADER => array(
//                    'Content-Type: application/json',
//                    'Authorization: Bearer 3686751bb23c4aed92e18fd096f5b18e'
//                ),
//            ));
//            $response = curl_exec($curl);
//            $err = curl_error($curl);
//            curl_close($curl);
//
//            if ($err) {
//                return response()->json(['success' => false, 'message' => 'Server error']);
//            } else {
////                dd(json_decode($response));
//                foreach (json_decode($response) as $item){
//                    $onload_city_ids[] = $item->CityId;
//                }
//                $onload_city_ids[] = (int) $data['onload_loc_id'];
//                $where[] = "g.onload_loc_id IN (".implode(",", $onload_city_ids).")";
//            }
//        }


//        if(isset($data['date_from']) && isset($data['date_to'])){
//            $where[] = "g.start_date >= '${data['date_from']}'";
//            $where[] = "g.end_date <= '${data['date_to']}'";
//        }else if(isset($data['date_from'])){
//            $where[] = "g.start_date >= '${data['date_from']}'";
//        }
//        if(isset($data['kuzov_type'])){
//
//            $data['kuzov_type'] = json_decode($data['kuzov_type']);
////            $ktypes = "'".implode("','",$data['kuzov_type'])."'";
//            foreach ($data['kuzov_type'] as $item){
//                $where[] = "g.kuzov_type LIKE '%${item}%'";
//            }
//        }
        $where[] = "g.is_disabled = '0'";
        if(!empty($where)){
            $where_text = implode(' AND ', $where);
        }

        if(strlen($where_text) != 0){
//            $sql = "SELECT * from `goods_orders` where ${where_text}";
//            $sql = "SELECT * from `goods_orders` where ${where_text}";
            $sql = "SELECT g.id, g.company_id, g.upload_loc_id, g.onload_loc_id,g.kuzov_type,
                              g.max_volume, IF(${data['is_subscribed']} = 1, managers.phone_number, NULL) AS manager_phone_number,
                            IF(${data['is_subscribed']} = 1, managers.FullName, NULL) AS manager_name,
                            IF(${data['is_subscribed']} = 1, g.company_name, NULL) AS company_name, g.is_disabled, g.created_at, IF(${data['is_subscribed']} = 1, g.description, NULL) AS order_description, g.manager_id,
                            upload.CityName AS upload_city_name, onload.CityName AS onload_city_name from `ride_orders` as g
                     JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                     JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                    JOIN managers managers ON g.manager_id = managers.id
                    where ${where_text} " ;
        }else{
            $sql =  "SELECT g.id, g.company_id, g.upload_loc_id, g.onload_loc_id,  g.kuzov_type,
                            g.max_volume, IF(${data['is_subscribed']} = 1, managers.phone_number, NULL) AS manager_phone_number,
                            IF(${data['is_subscribed']} = 1, managers.FullName, NULL) AS manager_name,
                            IF(${data['is_subscribed']} = 1, g.company_name, NULL) AS company_name, g.is_disabled, g.created_at, IF(${data['is_subscribed']} = 1, g.description, NULL) AS order_description, g.manager_id,
                            upload.CityName AS upload_city_name, onload.CityName AS onload_city_name from `ride_orders` as g
                     JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                     JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                     JOIN managers managers ON g.manager_id = managers.id;";
        }
        $sql .= "ORDER BY id LIMIT ${limit} OFFSET ${offset}";
        $aa = DB::select($sql);

        if(isset($data['kuzov_type'])){
            foreach ($aa as $key => $item){
                if(!$this->hasCommonValue(json_decode($item->kuzov_type), json_decode($data['kuzov_type']))){
                    unset($aa[$key]);
                }
            }
        }
        if(isset($data['upload_loc_id'])) {
            $cityUploadFromRequest = RussiaRegions::query()->where('CityId', $data['upload_loc_id'])->first();
            foreach ($aa as $key => $elem){
                $cityUploadFromDB = RussiaRegions::query()->where('CityId', $elem->upload_loc_id)->first();
                $cityUploadDistance = 0;
                $cityUploadDistance = ($this->calculateDistance($cityUploadFromDB['Longitude'], $cityUploadFromDB['Latitude'], $cityUploadFromRequest['Longitude'], $cityUploadFromRequest['Latitude']));
                if($cityUploadDistance >= $data['upload_loc_radius']){
                    unset($aa[$key]);
                }
            }
        }
        if(isset($data['onload_loc_id'])) {
            $cityOnloadFromRequest = RussiaRegions::query()->where('CityId', $data['onload_loc_id'])->first();
            foreach ($aa as $key => $elem){
                $cityOnloadFromDB = RussiaRegions::query()->where('CityId', $elem->onload_loc_id)->first();
                $cityOnloadDistance = 0;
                $cityOnloadDistance = ($this->calculateDistance($cityOnloadFromDB['Longitude'], $cityOnloadFromDB['Latitude'], $cityOnloadFromRequest['Longitude'], $cityOnloadFromRequest['Latitude']));
                if($cityOnloadDistance >= $data['onload_loc_radius']){
                    unset($aa[$key]);
                }
            }
        }
        $g = [];
        foreach ($aa as $f){
            $g[] = $f;
        }
        return response()->json(['success' => true, 'rides' => $g]);
    }


    function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius of the Earth in kilometers

        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLon / 2) * sin($deltaLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    function hasCommonValue($array1, $array2) {
        foreach ($array1 as $value1) {
            if (in_array($value1, $array2)) {
                return true;
            }
        }
        return false;
    }

    public function getOrders(\Illuminate\Http\Request $request){
        $data= $request->all();
        $offset = $request->all()['offset'] ?? 0;
        $limit =  $request->all()['limit'] ?? 10;

        if (!isset($data['upload_loc_radius'])){
            $data['upload_loc_radius'] = 5;
        }
        if (!isset($data['onload_loc_radius'])){
            $data['onload_loc_radius'] = 5;
        }
        if($data['upload_loc_radius'] > 300 || $data['onload_loc_radius'] > 300){
            return response()->json(['success' => false, 'message' => 'Параметр радиус должен быть больше 0 и меньше 300']);
        }

        $upload_city_ids = [];
        $onload_city_ids = [];
        $where_text =  '';
        $where = [];
//        if(isset($data['upload_loc_id'] )) {
//            $curl = curl_init();
//            curl_setopt_array($curl, array(
//                CURLOPT_URL => "https://api.ati.su/v1.0/dictionaries/cities/" . $data['upload_loc_id'] . "/near?count=20&radius=" . $data['upload_loc_radius'],// your preferred link
//                CURLOPT_RETURNTRANSFER => true,
//                CURLOPT_ENCODING => "",
//                CURLOPT_TIMEOUT => 30000,
//                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                CURLOPT_CUSTOMREQUEST => "GET",
//                CURLOPT_HTTPHEADER => array(
//                    // Set Here Your Requesred Headers
//                    'Content-Type: application/json',
//                    'Authorization: Bearer 3686751bb23c4aed92e18fd096f5b18e'
//                ),
//            ));
//            $response = curl_exec($curl);
//            $err = curl_error($curl);
//            curl_close($curl);
//
//            if ($err) {
//                return response()->json(['success' => false, 'message' => 'Server error']);
//            } else {
//                foreach (json_decode($response) as $item){
//                    $upload_city_ids[] = (int) $item->CityId;
//                }
//                $upload_city_ids[] = (int) $data['upload_loc_id'];
//
//                $where[] = "g.upload_loc_id IN (".implode(",", $upload_city_ids).")";
//            }
//        }
//        if(isset($data['onload_loc_id'] )) {
//            $curl = curl_init();
//            curl_setopt_array($curl, array(
//                CURLOPT_URL => "https://api.ati.su/v1.0/dictionaries/cities/" . $data['onload_loc_id'] . "/near?count=20&radius=" . $data['onload_loc_radius'],// your preferred link
//                CURLOPT_RETURNTRANSFER => true,
//                CURLOPT_ENCODING => "",
//                CURLOPT_TIMEOUT => 30000,
//                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                CURLOPT_CUSTOMREQUEST => "GET",
//                CURLOPT_HTTPHEADER => array(
//                    'Content-Type: application/json',
//                    'Authorization: Bearer 3686751bb23c4aed92e18fd096f5b18e'
//                ),
//            ));
//            $response = curl_exec($curl);
//            $err = curl_error($curl);
//            curl_close($curl);
//
//            if ($err) {
//                return response()->json(['success' => false, 'message' => 'Server error']);
//            } else {
////                dd(json_decode($response));
//                foreach (json_decode($response) as $item){
//                    $onload_city_ids[] = $item->CityId;
//                }
//                $onload_city_ids[] = (int) $data['onload_loc_id'];
//                $where[] = "g.onload_loc_id IN (".implode(",", $onload_city_ids).")";
//            }
//        }

        if(isset($data['start_date']) && isset($data['end_date'])){
            $where[] = "g.start_date >= '${data['start_date']}'";
            $where[] = "g.end_date <= '${data['end_date']}'";
        }else if(isset($data['start_date'])){
            $where[] = "g.start_date >= '${data['start_date']}'";
        }else if(isset($data['end_date'])){
            $where[] = "g.end_date <= '${data['end_date']}'";
        }

//        if(isset($data['kuzov_type'])){
//            $kuzovsArr = json_decode($data['kuzov_type']);
//            $kuzovs = join("','",$kuzovsArr);
////            foreach ($data['kuzov_type'] as $item){
//                $where[] = "g.kuzov_type IN ('${kuzovs}')";
////            }
////            $where[] = "g.kuzov_type LIKE '%${data['kuzov_type']}%'";
//        }

        if(isset($data['order_title'])){
            $titleArr = json_decode($data['order_title']);
            $titles = join("','",$titleArr);

//            dd($titleArr, $data['order_title']);
//            foreach ($data['order_title'] as $item) {
                $where[] = "g.order_title IN ('${titles}')";
//            }
        }
        if(isset($data['ruble_per_tonn'])){
            $where[] = "g.ruble_per_tonn >= '${data['ruble_per_tonn']}'";
        }
        if(isset($data['distance'])){
            $where[] = "g.distance = '${data['distance']}'";
        }

        if(isset($data['upload_region_id'])){
            $where[] = "g.upload_region_id = '${data['upload_region_id']}'";
        }
        if(isset($data['onload_region_id'])){
            $where[] = "g.onload_region_id = '${data['onload_region_id']}'";
        }
//        $upload_nearest_ids = [];
//        $onload_nearest_ids = [];

        if(!isset($data['upload_region_id']) && isset($data['upload_loc_id'])){
            $cityUploadFromRequest = RussiaRegions::query()->where('CityId', $data['upload_loc_id'])->first();
            $cities = RussiaRegions::all();
            foreach ($cities as $key => $elem) {
                $cityUploadDistance = 0;
                $cityUploadDistance = ($this->calculateDistance($elem['Latitude'],$elem['Longitude'], $cityUploadFromRequest['Latitude'], $cityUploadFromRequest['Longitude']));
                if ($cityUploadDistance <= $data['upload_loc_radius']) {
                    $upload_city_ids[] = $elem['CityId'];
                }
            }
            $upload_city_ids[] = $data['upload_loc_id'];
            $strUploadIds = implode(",", $upload_city_ids);
            $where[] = "g.upload_loc_id IN (${strUploadIds})";
        }
        if(!isset($data['onload_region_id']) && isset($data['onload_loc_id'])){
            $cityOnloadFromRequest = RussiaRegions::query()->where('CityId', $data['onload_loc_id'])->first();
            $cities = RussiaRegions::all();
            foreach ($cities as $key => $elem) {
                $cityOnloadDistance = 0;
                $cityOnloadDistance = ($this->calculateDistance($elem['Longitude'], $elem['Latitude'], $cityOnloadFromRequest['Longitude'], $cityOnloadFromRequest['Latitude']));
                if ($cityOnloadDistance <= $data['onload_loc_radius']) {
                    $onload_city_ids[] = $elem['CityId'];
                }
            }
            $onload_city_ids[] = $data['onload_loc_id'];
            $strOnloadIds = implode(",", $onload_city_ids);
            $where[] = "g.onload_loc_id IN (${strOnloadIds})";
        }

        $where[] = "g.is_disabled = '0'";
        if(!empty($where)){
            $where_text = implode(' AND ', $where);
        }
        $sql = "SELECT g.id, g.company_id, g.upload_loc_id, g.upload_region_id, g.onload_loc_id, g.onload_region_id, g.onload_loc_address, g.kuzov_type, g.loading_type, g.start_date, g.end_date,
                        g.max_volume, g.payment_type, g.payment_nds, g.ruble_per_tonn,IF(${data['is_subscribed']} = 1, managers.phone_number, NULL) AS manager_phone_number,
                        IF(${data['is_subscribed']} = 1, managers.FullName, NULL) AS manager_name, g.order_title,
                        IF(${data['is_subscribed']} = 1, g.company_name, NULL) AS company_name, g.is_disabled, g.created_at,
                        IF(${data['is_subscribed']} = 1, g.description, NULL) AS order_description, g.prepaid, g.manager_id, g.distance,
                        upload.CityName AS upload_city_name, onload.CityName AS onload_city_name from `goods_orders` as g
                 JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                 JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                 JOIN managers managers ON g.manager_id = managers.id
                where ${where_text}";

        $sql .= " ORDER BY is_disabled DESC LIMIT ${limit} OFFSET ${offset}";
        $aa = DB::select($sql);
        if(isset($data['kuzov_type'])){
            foreach ($aa as $key => $item){
                if(!$this->hasCommonValue(json_decode($item->kuzov_type), json_decode($data['kuzov_type']))){
                    unset($aa[$key]);
                }
            }
        }



        $g = [];
        foreach ($aa as $f){
            $g[] = $f;
        }
        return response()->json(['success' => true, 'orders' => $g]);


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

    /**
     * @throws \YooKassa\Common\Exceptions\NotFoundException
     * @throws \YooKassa\Common\Exceptions\ResponseProcessingException
     * @throws \YooKassa\Common\Exceptions\ApiException
     * @throws \YooKassa\Common\Exceptions\BadApiRequestException
     * @throws \YooKassa\Common\Exceptions\ExtensionNotFoundException
     * @throws \YooKassa\Common\Exceptions\AuthorizeException
     * @throws \YooKassa\Common\Exceptions\InternalServerError
     * @throws \YooKassa\Common\Exceptions\ForbiddenException
     * @throws \YooKassa\Common\Exceptions\TooManyRequestsException
     * @throws \YooKassa\Common\Exceptions\ApiConnectionException
     * @throws \YooKassa\Common\Exceptions\UnauthorizedException
     */
    public function paymentApi(Request $request){
        $client = new Client();
        $client->setAuth('318022', 'live_rlyBFDJSbU_haHH2256XICLIDYs0-LJgvkRDVSel6EI');
//        dd($client->me());
        $idempotenceKey = uniqid('', true);
        $response = $client->createPayment(
            array(
                'amount' => array(
                    'value' => '2.00',
                ),
                'capture' => false,
                'payment_method_data' => array(
                    'type' => 'bank_card',
                ),
                'confirmation' => array(
                    'type' => 'redirect',
                    'return_url' => 'https://transagro.pro/unauthorized',
                ),
                'description' => 'Заказ №72',
            ),
            $idempotenceKey
        );

        //get confirmation url
        $confirmationUrl = $response->getConfirmation()->getConfirmationUrl();
        dd($confirmationUrl);
//        return response()->json(['data' => $payment->]);
    }

    public function resetPassword(\Illuminate\Http\Request $request){
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ])->header('Status-Code', 200);
        }
        $user = Auth::user();
        $user->password = Hash::make($request->all()['password']);
        $user->save();
        return response()->json(['success' => true]);
    }


}

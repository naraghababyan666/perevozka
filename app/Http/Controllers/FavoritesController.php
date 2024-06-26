<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Favorites;
use App\Models\Subscriptions;
use App\Models\Transactions;
use App\Service\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FavoritesController extends Controller
{
    public function user(Request $request){
        $user = Auth::user();
        $config = DB::table('configs')->where('id', 1)->first();
        $tariff_price_str = 'tariff_price_' . Auth::user()['role_id'];
        $tariff_name_str= 'tariff_name_' . Auth::user()['role_id'];

        $this->makeSubscription();
        $data = Subscriptions::query()->where('company_id', Auth::id())->where('valid_until', '>', Carbon::now())->first();
        if(!is_null($data)){
            $user['valid_until'] = $data['valid_until'];
            $user['isSubscribed'] = 1;
        }
//        $user['isSubscribed'] = 1;

        $tariff = [];
        $tariff['tariff_name'] = $config->$tariff_name_str;
        $tariff['price'] = $config->$tariff_price_str;
        $tariff['role_id'] = Auth::user()['role_id'];
        $user['tariff'] = $tariff;

//        $user['tariff']['tariff_name'] = $config->$tariff_name_str;

        return response()->json(['user' => $user]);

    }

    public function makeSubscription(){
        $service = new PaymentService();
//        $data = Subscriptions::query()->where('company_id', Auth::id())->where('valid_until', '>', Carbon::now())->first();
        $transactions = Transactions::query()->where('company_id', Auth::id())->get();
        if(!is_null($transactions)){
            foreach ($transactions as $transaction){
                $service->checkPaymentForMobile($transaction['order_id']);
            }
        }

    }

    public function getFavoritesList(){
        $subscribed = 0;
        $data = Subscriptions::query()->where('company_id', Auth::id())->where('valid_until', '>', Carbon::now())->first();
        if(!is_null($data)){
            $subscribed = 1;
        }
        $user = Auth::user();
        $result = [];
//        if(is_null($user['favorite_ride'])){
//            $user['favorite_ride'] = [];
//        }else if(is_null($user['favorite_goods'])){
//            $user['favorite_goods'] = [];
//
//        }
        if(Auth::user()['role_id'] == Company::IS_OWNER){
            $ride = $user['favorite_ride'];
            if(!is_null($user['favorite_ride'])){
                $rideStr = implode(",", json_decode($ride));
            }else{
                $rideStr = [];
            }
            if(!is_null($ride)){
                $sql =  "SELECT g.id, g.company_id, g.upload_loc_id, g.onload_loc_id, g.kuzov_type,
                            g.max_volume, IF(${subscribed} = 1, managers.phone_number, NULL) AS manager_phone_number,
                            IF(${subscribed} = 1, managers.FullName, NULL) AS manager_name,
                            g.company_name, g.is_disabled, g.created_at, IF(${subscribed} = 1, g.description, NULL) AS order_description, g.manager_id,
                            upload.CityName AS upload_city_name, onload.CityName AS onload_city_name
                 from `ride_orders` as g
                 JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                 JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                 JOIN managers managers ON g.manager_id = managers.id
                WHERE `is_disabled` = '0' AND g.id IN (${rideStr});
                ";
                $result['ride'] = DB::select($sql);

            }

        }
        else if(Auth::user()['role_id'] == Company::IS_DRIVER){
            $goods = $user['favorite_goods'];
            if(!is_null($user['favorite_goods'])){
                $goodsStr = implode(",", json_decode($goods));
            }else{
                $goodsStr = [];
            }
            if(!is_null($goods)){
                $sql =  "SELECT g.id, g.company_id, g.upload_loc_id, g.onload_loc_id, g.onload_loc_address, g.order_title, g.kuzov_type, g.loading_type, g.start_date, g.end_date,
                            g.max_volume, g.payment_type, g.payment_nds, g.ruble_per_tonn,IF(${subscribed} = 1, managers.phone_number, NULL) AS manager_phone_number,
                            IF(${subscribed} = 1, managers.FullName, NULL) AS manager_name,
                            g.company_name, g.is_disabled, g.created_at, IF(${subscribed} = 1, g.description, NULL) AS order_description, g.prepaid, g.manager_id,
                            g.distance, upload.CityName AS upload_city_name, onload.CityName AS onload_city_name
                 from `goods_orders` as g
                 JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                 JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                 JOIN managers managers ON g.manager_id = managers.id
                WHERE `is_disabled` = '0' AND g.id IN (${goodsStr});
                ";
                $result['goods'] = DB::select($sql);
            }

        }
        else if(Auth::user()['role_id'] == Company::IS_OWNER_AND_DRIVER){
            $goods = json_decode($user['favorite_goods']);
            $ride = json_decode($user['favorite_ride']);
            if(!is_null($user['favorite_ride'])){
                $rideStr = implode(",", ($ride));
            }else{
                $rideStr = [];
            }
            if(!is_null($user['favorite_goods'])){
                $goodsStr = implode(",", ($goods));
            }else{
                $goodsStr = [];
            }
            if(!is_null($goods)){
                $sqlGoods =  "SELECT g.id, g.company_id, g.upload_loc_id, g.onload_loc_id, g.onload_loc_address, g.order_title, g.kuzov_type, g.loading_type, g.start_date, g.end_date,
                            g.max_volume, g.payment_type, g.payment_nds, g.ruble_per_tonn,IF(${subscribed} = 1, managers.phone_number, NULL) AS manager_phone_number,
                            IF(${subscribed} = 1, managers.FullName, NULL) AS manager_name,
                            g.company_name, g.is_disabled, g.created_at, IF(${subscribed} = 1, g.description, NULL) AS order_description, g.prepaid, g.manager_id,
                            g.distance, upload.CityName AS upload_city_name, onload.CityName AS onload_city_name
                 from `goods_orders` as g
                 JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                 JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                 JOIN managers managers ON g.manager_id = managers.id
                WHERE `is_disabled` = '0' AND g.id IN (${goodsStr}); ;
                ";
                $result['goods'] = DB::select($sqlGoods);
            }
            if(!is_null($ride)){
                $sqlRide =  "SELECT g.id, g.company_id, g.upload_loc_id, g.onload_loc_id, g.kuzov_type,
                            g.max_volume, IF(${subscribed} = 1, managers.phone_number, NULL) AS manager_phone_number,
                            IF(${subscribed} = 1, managers.FullName, NULL) AS manager_name,
                            g.company_name, g.is_disabled, g.created_at, IF(${subscribed} = 1, g.description, NULL) AS order_description, g.manager_id,
                            upload.CityName AS upload_city_name, onload.CityName AS onload_city_name
                 from `ride_orders` as g
                 JOIN russia_regions upload ON g.upload_loc_id = upload.CityId
                 JOIN russia_regions onload ON g.onload_loc_id = onload.CityId
                 JOIN managers managers ON g.manager_id = managers.id
                WHERE `is_disabled` = '0' AND g.id IN (${rideStr});
                ";
                $result['ride'] = DB::select($sqlRide);

            }

//            $rideList = DB::select($sqlRide);
//            $goodsList = DB::select($sqlGoods);
//            $result['ride'][] = $rideList;
//            $result['goods'][] = $goodsList;

        }

        $companyIds = $user['favorite_companies'];
        $companyStr = '';
        if(!is_null($user['favorite_companies'])){
            $goodsStr = implode(",", json_decode($companyIds));
        }else{
            $goodsStr = [];
        }
        if(!is_null(json_decode($companyIds))){
            $companiesList = Company::query()->whereIn('id', json_decode($companyIds))->get();
            $result['companies'] = $companiesList;
        }else{
            $result['companies'] = [];

        }

        return response()->json(['success' => true, 'list' => $result]);




//        $favorites = Favorites::query()->where('company_id', Auth::id());
//        if (Auth::user()['role_id'] == Company::IS_OWNER){
//            $favorites->where('order_type', '=', 'ride')->with('rides')->get();
//        }else if(Auth::user()['role_id'] == Company::IS_DRIVER){
//            $favorites->where('order_type', '=', 'good')->with('goods')->get();
//        }else{
//            $favorites->with(['goods', 'rides'])->get();
//        }
//        $favorites = $favorites->get();
//        foreach ($favorites as $item){
//            if(!is_null($item->rides)){
//                if($subscribed == 0){
//                    unset($item->rides['phone_number']);
//                }
//                $item->rides->upload_city_name = ((new \App\Models\RussiaRegions)->getCityNameById($item->rides->upload_loc_id));
//                $item->rides->onload_city_name = ((new \App\Models\RussiaRegions)->getCityNameById($item->rides->onload_loc_id));
//            }else if(!is_null($item->goods)){
//                if($subscribed == 0){
//                    unset($item->goods['phone_number']);
//                }
//                $item->goods->upload_city_name = ((new \App\Models\RussiaRegions)->getCityNameById($item->goods->upload_loc_id));
//                $item->goods->onload_city_name = ((new \App\Models\RussiaRegions)->getCityNameById($item->goods->onload_loc_id));
//            }
//
//        }



    }

    public function addToFavoriteGoods(Request $request){
        if(in_array(Auth::user()['role_id'], [2,3])){
            $validator = Validator::make($request->all(), [
                'goods_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    "errors" => $validator->errors()
                ])->header('Status-Code', 200);
            }
            $user = Company::query()->where('id', Auth::id())->first();
            $ids = [];
            if (is_null($user['favorite_goods'])){
                $ids[] = $validator->validated()['goods_id'];
                $user['favorite_goods'] = json_encode($ids);
            }else{
                $list = json_decode($user['favorite_goods']);
                $list[] = $validator->validated()['goods_id'];
                $list = array_unique($list);
                $user['favorite_goods'] = json_encode($list);
            }
            $user->save();
            return response()->json(['success' => true, 'message' => 'Goods successfully added to favorite list']);

        }else{
            return response()->json(['success' => false, 'message' => 'Permission denied!'], 403);

        }
    }

    public function addToFavoriteRide(Request $request)
    {
        if (in_array(Auth::user()['role_id'], [1, 3])) {
            $validator = Validator::make($request->all(), [
                'ride_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    "errors" => $validator->errors()
                ])->header('Status-Code', 200);
            }
            $user = Company::query()->where('id', Auth::id())->first();
            $ids = [];
            if (is_null($user['favorite_ride'])) {
                $ids[] = $validator->validated()['ride_id'];
                $user['favorite_ride'] = json_encode($ids);
            } else {
                $list = json_decode($user['favorite_ride']);
                $list[] = $validator->validated()['ride_id'];
                $list = array_unique($list);
                $user['favorite_ride'] = json_encode($list);
            }
            $user->save();
            return response()->json(['success' => true, 'message' => 'Ride successfully added to favorite list']);
        } else {
            return response()->json(['success' => false, 'message' => 'Permission denied!'], 403);
        }

    }
    public function addToFavoriteCompany(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'company_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    "errors" => $validator->errors()
                ])->header('Status-Code', 200);
            }
            $user = Company::query()->where('id', Auth::id())->first();
            $ids = [];
            if (is_null($user['favorite_companies'])) {
                $ids[] = $validator->validated()['company_id'];
                $user['favorite_companies'] = json_encode($ids);
            } else {
                $list = json_decode($user['favorite_companies']);
                $list[] = $validator->validated()['company_id'];
                $list = array_unique($list);
                $user['favorite_companies'] = json_encode($list);
            }
            $user->save();
            return response()->json(['success' => true, 'message' => 'Company successfully added to favorite list']);


    }

    public function deleteFromFavoriteList(Request $request){
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'order_type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ])->header('Status-Code', 200);
        }
        $user = Company::query()->where('id', Auth::id())->first();
        if($validator->validated()['order_type'] == 'ride'){
            $list = json_decode($user['favorite_ride']);
//            array_diff($list, [$validator->validated()['order_id']]);
            if(!is_null($list)){

                if (($key = array_search($validator->validated()['order_id'], $list)) !== false) {
                    unset($list[$key]);
                }
                $values = array_values($list);
                if(count($values) != 0){
                    $user['favorite_ride'] = json_encode($values);
                }else{
                    $user['favorite_ride'] = null;
                }
                $user->save();
            }else{
                return response()->json(['success' => false, 'message' => 'Invalid id']);
            }
        }else if ($validator->validated()['order_type'] == 'goods'){
            $list = json_decode($user['favorite_goods']);
//            dd($list);
            if(!is_null($list)){
                if (($key = array_search($validator->validated()['order_id'], $list)) !== false) {
                    unset($list[$key]);
                }
                $values = array_values($list);
                if(count($values) != 0){
                    $user['favorite_goods'] = json_encode($values);
                }else{
                    $user['favorite_goods'] = null;
                }
                $user->save();
            }else{
                return response()->json(['success' => false, 'message' => 'Invalid id']);
            }

        }else if ($validator->validated()['order_type'] == 'company'){
            $list = json_decode($user['favorite_companies']);
//            dd($list);
            if(!is_null($list)){
                if (($key = array_search($validator->validated()['order_id'], $list)) !== false) {
                    unset($list[$key]);
                }
                $values = array_values($list);
                if(count($values) != 0){
                    $user['favorite_companies'] = json_encode($values);
                }else{
                    $user['favorite_companies'] = null;
                }
                $user->save();
            }else{
                return response()->json(['success' => false, 'message' => 'Invalid id']);
            }
        }
        else{
            return response()->json(['success' => false, 'message' => 'Key not found']);

        }
        return response()->json(['success' => true, 'message' => 'Order successfully deleted from favorite list']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Favorites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FavoritesController extends Controller
{
    public function user(){
        $user = Auth::user();
        return response()->json(['user' => $user]);

    }

    public function getFavoritesList(){
        $favorites = Favorites::query()->where('company_id', Auth::id());
        if (Auth::user()['role_id'] == Company::IS_OWNER){
            $favorites->where('order_type', '=', 'ride')->with('rides')->get();
        }else if(Auth::user()['role_id'] == Company::IS_DRIVER){
            $favorites->where('order_type', '=', 'good')->with('goods')->get();
        }else{
            $favorites->with(['goods', 'rides'])->get();
        }
        $favorites = $favorites->get();
        foreach ($favorites as $item){
            if(!is_null($item->rides)){
                $item->rides->upload_city_name = ((new \App\Models\RussiaRegions)->getCityNameById($item->rides->upload_loc_id));
                $item->rides->onload_city_name = ((new \App\Models\RussiaRegions)->getCityNameById($item->rides->onload_loc_id));
            }else if(!is_null($item->goods)){
                $item->goods->upload_city_name = ((new \App\Models\RussiaRegions)->getCityNameById($item->goods->upload_loc_id));
                $item->goods->onload_city_name = ((new \App\Models\RussiaRegions)->getCityNameById($item->goods->onload_loc_id));
            }

        }

        return response()->json(['success' => true, 'list' => $favorites]);

    }

    public function addToFavorite(Request $request){
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
        Favorites::query()->create([
            'company_id' => Auth::id(),
            'order_id' => $validator->validated()['order_id'],
            'order_type' => $validator->validated()['order_type']
        ]);
        return response()->json(['success' => true, 'message' => 'Order successfully added to favorite list']);
    }

    public function deleteFromFavoriteList(Request $request){
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:goods_orders,id',
            'order_type' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ])->header('Status-Code', 200);
        }
        $order = Favorites::query()
            ->where('company_id', Auth::id())
            ->where('order_id', $validator->validated()['order_id'])
            ->where('order_type', $validator->validated()['order_type'])->first();
        if(!is_null($order)){
            $order->delete();
            return response()->json(['success' => true, 'message' => 'Order successfully deleted from favorite list']);
        }
        return response()->json(['success' => false, 'message' => 'Server error']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\GoodsOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GoodsOrdersController extends Controller
{
    public function createOrder(Request $request){

        $validator = Validator::make($request->all(), [
            'upload_loc_id' => 'required',
            'onload_loc_id' => 'required',
            'kuzov_type' => 'required',
            'loading_type' => 'required',
            'loading_date' => 'required',
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

        $goods_orders = new GoodsOrders();
        $goods_orders->company_id = Auth::id();
        $goods_orders->upload_loc_id = $validator->validated()['upload_loc_id'];
        $goods_orders->onload_loc_id = $validator->validated()['onload_loc_id'];
        $goods_orders->kuzov_type = $validator->validated()['kuzov_type'];
        $goods_orders->loading_type = $validator->validated()['loading_type'];
        $goods_orders->loading_date = $validator->validated()['loading_date'];
        $goods_orders->max_weight = $validator->validated()['max_weight'];
        $goods_orders->max_volume = $validator->validated()['max_volume'];
        $goods_orders->payment_type = $validator->validated()['payment_type'];
        $goods_orders->ruble_per_kg = $validator->validated()['ruble_per_kg'];
        $goods_orders->phone_number = $validator->validated()['phone_number'];
        $goods_orders->company_name = $validator->validated()['company_name'];
        $goods_orders->is_disabled = '0';
        $goods_orders->save();
//        $data =  DB::table('goods_orders')->latest()->first();

        return response()->json(['success' => true, 'message' => 'Your order successfully created']);

    }
}

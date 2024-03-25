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


        if($request->all()['payment_type'] == 'Без нал'){
            $validator = Validator::make($request->all(), [
                'upload_loc_id' => 'required',
                'onload_loc_id' => 'required',
                'upload_region_id' => 'required',
                'onload_region_id' => 'required',
                'order_title' => 'required',
                'kuzov_type' => 'required',
                'loading_type' => 'required',
                'max_volume' => 'required',
                'payment_type' => 'required',
                'payment_nds' => 'required',
                'prepaid' => 'required',
                'ruble_per_tonn' => 'required',
                'company_name' => 'required',
                'manager_id' => 'required',

            ]);
        }else{

            $validator = Validator::make($request->all(), [
                'upload_loc_id' => 'required',
                'onload_loc_id' => 'required',
                'upload_region_id' => 'required',
                'onload_region_id' => 'required',
                'order_title' => 'required',
                'kuzov_type' => 'required',
                'loading_type' => 'required',
                'max_volume' => 'required',
                'payment_type' => 'required',
                'prepaid' => 'required',
                'ruble_per_tonn' => 'required',
                'company_name' => 'required',
                'manager_id' => 'required',

            ]);
        }
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ])->header('Status-Code', 203);
        }

        $goods_orders = new GoodsOrders();
        $goods_orders->company_id = Auth::id();
        $goods_orders->upload_loc_id = $validator->validated()['upload_loc_id'];
        $goods_orders->onload_loc_id = $validator->validated()['onload_loc_id'];
        $goods_orders->onload_region_id = $validator->validated()['onload_region_id'];
        $goods_orders->upload_region_id = $validator->validated()['upload_region_id'];
        $goods_orders->onload_loc_address = $request->all()['onload_loc_address'] ?? null;
        $goods_orders->distance = $request->all()['distance'] ?? null;
        $goods_orders->order_title = $validator->validated()['order_title'];
        $goods_orders->kuzov_type = ($validator->validated()['kuzov_type']);
        $goods_orders->loading_type = ($validator->validated()['loading_type']);
        $goods_orders->start_date = $request->all()['start_date'];
        $goods_orders->end_date = $request->all()['end_date'];
        $goods_orders->max_volume = $validator->validated()['max_volume'];
        $goods_orders->payment_type = $validator->validated()['payment_type'];
        if($request->all()['payment_type'] == 'Без нал'){
            $goods_orders->payment_nds = $validator->validated()['payment_nds'];
        }
        $goods_orders->prepaid = $validator->validated()['prepaid'];
        $goods_orders->ruble_per_tonn = $validator->validated()['ruble_per_tonn'];
        $goods_orders->company_name = $validator->validated()['company_name'];
        $goods_orders->description = $request->all()['description'] ?? null;
        $goods_orders->manager_id = $validator->validated()['manager_id'];
        $goods_orders->is_disabled = '0';
        $goods_orders->save();
//        $data =  DB::table('goods_orders')->latest()->first();

        return response()->json(['success' => true, 'message' => 'Your order successfully created']);

    }

    public function updateOrder(Request $request, $id){
        if($request->all()['payment_type'] == 'Без нал'){
            $validator = Validator::make($request->all(), [
                'upload_loc_id' => 'required',
                'onload_loc_id' => 'required',
                'upload_region_id' => 'required',
                'onload_region_id' => 'required',
                'order_title' => 'required',
                'kuzov_type' => 'required',
                'loading_type' => 'required',
                'max_volume' => 'required',
                'payment_type' => 'required',
                'payment_nds' => 'required',
                'prepaid' => 'required',
                'ruble_per_tonn' => 'required',
                'company_name' => 'required',
                'manager_id' => 'required',

            ]);
        }else{

            $validator = Validator::make($request->all(), [
                'upload_loc_id' => 'required',
                'onload_loc_id' => 'required',
                'upload_region_id' => 'required',
                'onload_region_id' => 'required',
                'order_title' => 'required',
                'kuzov_type' => 'required',
                'loading_type' => 'required',
                'max_volume' => 'required',
                'payment_type' => 'required',
                'prepaid' => 'required',
                'ruble_per_tonn' => 'required',
                'company_name' => 'required',
                'manager_id' => 'required',

            ]);
        }
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ])->header('Status-Code', 203);
        }
        $goods_orders = GoodsOrders::query()->findOrFail($id);
        $goods_orders->company_id = Auth::id();
        $goods_orders->upload_loc_id = $validator->validated()['upload_loc_id'];
        $goods_orders->onload_loc_id = $validator->validated()['onload_loc_id'];
        $goods_orders->onload_region_id = $validator->validated()['onload_region_id'];
        $goods_orders->upload_region_id = $validator->validated()['upload_region_id'];
        $goods_orders->onload_loc_address = $request->all()['onload_loc_address'] ?? null;
        $goods_orders->distance = $request->all()['distance'] ?? null;
        $goods_orders->order_title = $validator->validated()['order_title'];
        $goods_orders->kuzov_type = ($validator->validated()['kuzov_type']);
        $goods_orders->loading_type = ($validator->validated()['loading_type']);
        $goods_orders->start_date = $request->all()['start_date'];
        $goods_orders->end_date = $request->all()['end_date'];
        $goods_orders->max_volume = $validator->validated()['max_volume'];
        $goods_orders->payment_type = $validator->validated()['payment_type'];
        if($request->all()['payment_type'] == 'Без нал'){
            $goods_orders->payment_nds = $validator->validated()['payment_nds'];
        }
        $goods_orders->prepaid = $validator->validated()['prepaid'];
        $goods_orders->ruble_per_tonn = $validator->validated()['ruble_per_tonn'];
        $goods_orders->company_name = $validator->validated()['company_name'];
        $goods_orders->description = $request->all()['description'] ?? null;
        $goods_orders->manager_id = $validator->validated()['manager_id'];
        $goods_orders->is_disabled = '0';
        $goods_orders->save();

//        $goods->update($request->all());
//        $data =  DB::table('goods_orders')->latest()->first();

        return response()->json(['success' => true, 'message' => 'Your order successfully updated']);

    }
}

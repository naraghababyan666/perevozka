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
            'order_title' => 'required',
            'kuzov_type' => 'required',
            'loading_type' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
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
            ])->header('Status-Code', 203);
        }

        $goods_orders = new GoodsOrders();
        $goods_orders->company_id = Auth::id();
        $goods_orders->upload_loc_id = $validator->validated()['upload_loc_id'];
        $goods_orders->upload_loc_info = $request->all()['upload_loc_info'] ?? null;
        $goods_orders->onload_loc_id = $validator->validated()['onload_loc_id'];
        $goods_orders->onload_loc_info = $request->all()['onload_loc_info'] ?? null;
        $goods_orders->order_title = $validator->validated()['order_title'];
        $goods_orders->kuzov_type = $validator->validated()['kuzov_type'];
        $goods_orders->loading_type = $validator->validated()['loading_type'];
        $goods_orders->start_date = $validator->validated()['start_date'];
        $goods_orders->end_date = $validator->validated()['end_date'];
        $goods_orders->max_weight = $validator->validated()['max_weight'];
        $goods_orders->max_volume = $validator->validated()['max_volume'];
        $goods_orders->payment_type = $validator->validated()['payment_type'];
        $goods_orders->payment_nds = $validator->validated()['payment_nds'];
        $goods_orders->prepaid = $validator->validated()['prepaid'];
        $goods_orders->ruble_per_kg = $validator->validated()['ruble_per_kg'];
        $goods_orders->company_name = $validator->validated()['company_name'];
        $goods_orders->description = $request->all()['description'] ?? null;
        $goods_orders->manager_id = $validator->validated()['manager_id'];
        $goods_orders->material_type = $validator->validated()['material_type'];
        $goods_orders->material_info = $validator->validated()['material_info'];
        $goods_orders->is_disabled = '0';
        $goods_orders->save();
//        $data =  DB::table('goods_orders')->latest()->first();

        return response()->json(['success' => true, 'message' => 'Your order successfully created']);

    }
}

<?php

namespace App\Http\Controllers;

use App\Models\GoodsOrders;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function makeOrderDisable($id){
        $goodsOrder = GoodsOrders::query()->find($id);
        if(!is_null($goodsOrder)){
            $goodsOrder->is_disabled = 1;
            return response()->json(['success' => true, 'message' => 'Order get']);
        }
        return response()->json(['success' => false, 'message' => 'Order not found'], 404);
    }
}

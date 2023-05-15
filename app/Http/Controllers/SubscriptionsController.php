<?php

namespace App\Http\Controllers;

use App\Models\Subscriptions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionsController extends Controller
{
    public function subscribe(Request $request){
        $data= Validator::make($request->all(),[
            'company_id' => 'required',
            'valid_until' => 'required|date_format:Y-m-d',
            'role_id' => 'required'
        ]);
        if ($data->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $data->errors()
            ])->header('Status-Code', 200);
        }
        if($data->validated()['valid_until'] < Carbon::now()){
            return response()->json([
                'success' => false,
                "errors" => 'Invalid date time'
            ])->header('Status-Code', 200);
        }
        $newData = Subscriptions::query()->create($data->validated());
        return response()->json(['success' => true]);
    }
}

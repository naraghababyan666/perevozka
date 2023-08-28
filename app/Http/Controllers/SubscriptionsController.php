<?php

namespace App\Http\Controllers;

use App\Models\Subscriptions;
use App\Models\Transactions;
use App\Service\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use YooKassa\Client;

class SubscriptionsController extends Controller
{

    public function index(){

    }
    public function create(Request $request, PaymentService $service){
        $amount = (float)$request->input('amount');
        $description = $request->input('description') ?? '';

        $transaction = Transactions::query()->create([
            'amount' => $amount,
            'description' => $description
        ]);
        if($transaction){
            $link = $service->createPayment($amount, $description, [
                'transaction_id' => $transaction->id
            ]);
        }
        return response()->json(['success' => true, 'link' => $link]);

    }


    public function subscribe(Request $request, PaymentService $service){
        $data= Validator::make($request->all(),[
            'company_id' => 'required',
            'valid_until' => 'required|date_format:Y-m-d',
            'role_id' => 'required',
            'order_id' => 'required'
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
        $paymentId = $request->input('order_id');
        $result = $service->checkPayment($paymentId);
        if($result){
            $newData = Subscriptions::query()->create([
                'company_id' => $request->input('company_id'),
                'valid_until' => $request->input('valid_until'),
                'role_id' => $request->input('role_id'),
            ]);
            return response()->json(['success' => true]);
        }
//        return response()->json(['success' => false, 'message' => 'Payment error']);

    }


}

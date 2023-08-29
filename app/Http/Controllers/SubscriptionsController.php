<?php

namespace App\Http\Controllers;

use App\Models\Subscriptions;
use App\Models\Transactions;
use App\Service\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use YooKassa\Client;

class SubscriptionsController extends Controller
{

    public function index(){

    }
    public function create(Request $request, PaymentService $service){
        $tariff = DB::table('tariff')->where('role_id', '=', Auth::user()['role_id'])->first();
        $amount = $tariff->price;
        $description = $request->input('description') ?? '';

//        $transaction = Transactions::query()->create([
//            'amount' => $amount,
//            'description' => $description
//        ]);

//        if($transaction){
        $link = $service->createPayment($amount, $description);
//        }
        return response()->json(['success' => true, 'link' => $link]);

    }


    public function subscribe(Request $request, PaymentService $service){
        $data= Validator::make($request->all(),[
            'order_id' => 'required'
        ]);
        if ($data->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $data->errors()
            ])->header('Status-Code', 200);
        }
//        $paymentId = $request->input('order_id');
        $paymentId = Transactions::query()->where('company_id', Auth::id())->latest('created_at')->first();
        $result = $service->checkPayment($paymentId['order_id']);
        if($result){
            $ifHasSubscriptions = Subscriptions::query()->where('company_id', Auth::id())->orderByDesc('valid_until')->first();
            if (!is_null($ifHasSubscriptions)){
                if ($ifHasSubscriptions['valid_until'] < Carbon::now()){
                    Subscriptions::query()->create([
                        'company_id' => Auth::id(),
                        'valid_until' => Carbon::now()->addMonth(),
                        'role_id' => $paymentId['role_id'],
                    ]);
                }else{
                    Subscriptions::query()->create([
                        'company_id' => Auth::id(),
                        'valid_until' => Carbon::parse($ifHasSubscriptions['valid_until'])->addMonth(),
                        'role_id' => $paymentId['role_id'],
                    ]);
                }
            }else{
                Subscriptions::query()->create([
                    'company_id' => Auth::id(),
                    'valid_until' => Carbon::now()->addMonth(),
                    'role_id' => $paymentId['role_id'],
                ]);
            }

            return response()->json(['success' => true]);
        }
//        return response()->json(['success' => false, 'message' => 'Payment error']);

    }


}

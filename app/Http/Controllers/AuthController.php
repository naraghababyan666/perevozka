<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\Company;
use App\Models\Subscriptions;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;

class AuthController extends Controller
{

    public function registration(CompanyRequest $request){
        try {
            $newCompany = Company::create([
                'email' => $request['email'],
                'phone_number' => $request['phone_number'],
                'password' => Hash::make($request['password']),
                'company_name' => $request['company_name'],
                'legal_address' => $request['legal_address'],
                'postal_address' => $request["postal_address"],
                'role_id' => $request['role_id'],
            ]);
            if (isset($request["inn"])) {
                $newCompany->inn = $request["inn"];
            }
            if (isset($request["ogrn"])) {
                $newCompany->ogrn = $request["ogrn"];
            }
            if (isset($request["logo_url"])) {
                $newCompany->logo_url = $request["logo_url"];
            }
            if (isset($request["favorites"])) {
                $newCompany->favorites = $request["favorites"];
            }
            $newCompany->save();
            $now = Carbon::now();
            $addSubscription = $now->addWeek(2);
            Subscriptions::query()->create([
                'company_id' => $newCompany->id,
                'valid_until' => $addSubscription->format('Y-m-d'),
                'role_id' => $newCompany->role_id
            ]);
            Auth::login($newCompany);
            $user = Auth::user();
            $token = $user->createToken($request["email"], ['server:update']);
            $user["api_token"] = $token->plainTextToken;
            $data = Subscriptions::query()->where('company_id', Auth::id())->where('valid_until', '>', Carbon::now())->first();
            if(!is_null($data)){
                $user['valid_until'] = $data['valid_until'];
            }
            $data = [
                'success' => true,
                'data' => $user
            ];
            return
                response($data)->setStatusCode(200)->header('Status-Code', '200');

        } catch (Exception $e) {

            throw new HttpResponseException(response()->json([
                'message' => $e->getMessage(),
            ],  $e->getCode())->header('Status-Code', $e->getCode()));
        }
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), ['email' => 'required|email', 'password' => 'required']);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ])->header('Status-Code', 200);
        }
        $validUser = Auth::attempt(['email' => $request["email"], 'password' => $request["password"]]);
        if ($validUser) {
            $user = Auth::getProvider()->retrieveByCredentials(['email' => $request["email"], 'password' => $request["password"]]);
            Auth::login($user);
            $token = $user->createToken($request["email"], ['server:update']);
            $user["api_token"] = $token->plainTextToken;
            $data = Subscriptions::query()->where('company_id', Auth::id())->where('valid_until', '>', Carbon::now())->first();
            if(!is_null($data)){
                $user['valid_until'] = $data['valid_until'];
            }
            $data = [
                'success' => true,
                'company' =>$user,
            ];
            return response($data)
                ->setStatusCode(200)->header('Status-Code', '200');

        } else {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Invalid user',
            ], 401)->header('Status-Code', '401'));
        }
    }
}

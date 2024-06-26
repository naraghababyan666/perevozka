<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Models\Company;
use App\Models\Review;
use App\Models\Subscriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function confirmReview($id){
        $review = Review::query()->find($id);
        $success = false;
        $message = 'Review not found!';
        if(!is_null($review)){
            $review->is_published = Review::CONFIRMED;
            $review->save();
            $success = true;
            $message = 'Review confirmed!';
        }
        return response()->json(['success' => $success, 'message' => $message]);

    }
    public function declineReview($id){
        $review = Review::query()->find($id);
        $success = false;
        $message = 'Review not found!';
        if(!is_null($review)){
            $review->is_published = Review::DECLINED;
            $review->save();
            $success = true;
            $message = 'Review declined!';
        }
        return response()->json(['success' => $success, 'message' => $message]);

    }

    public function reviewList(){
        $reviews = Review::with('writer', 'company')->get();
        return response()->json(['success' => true, 'reviews' => $reviews]);
    }

    public function deleteUser($id){
        if(!is_null(Company::query()->where('id', $id)->first())){
            Company::query()->where('id', $id)->with('manager', 'goods', 'rides', 'subscriptions')->delete();
            return response()->json(['success' => true]);
        }else{
            return response()->json(['error' => 'User not found']);
        }
    }

    public function changeSubscription(Request $request){
        $validator = Validator::make($request->all(), [
            'company_id' => 'required',
            'valid_until' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $validator->errors()
            ])->header('Status-Code', 203);
        }
        Subscriptions::query()->where('company_id', $validator->validated()['company_id'])->delete();
        $data = Subscriptions::query()->create([
            'company_id' => $validator->validated()['company_id'],
            'valid_until' => $validator->validated()['valid_until'],
            'role_id' => Company::query()->where('id', $validator->validated()['company_id'])->first()['role_id']
        ]);

        return response()->json(['success' => true]);

    }

    public function sendMail(Request $request){
        $text = $request->all()['text'] ?? `Уважаемые пользователи!
Команда Transagro сообщает об обновлении приложения.`;

        $users = Company::all();
        foreach ($users as $user){
            if(filter_var($user['email'], FILTER_VALIDATE_EMAIL)){
                try{
                    Mail::to($user['email'])->send(new SendMail($text));
                }
                catch (\Exception $exception){
//                    dd($exception->getMessage());
                    continue;
                }
            }


        }
        return response()->json(['success' => true]);
    }
}

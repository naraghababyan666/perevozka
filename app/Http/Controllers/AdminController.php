<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

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
}

<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function create(Request $request)
    {
        $data = Validator::make($request->all(), [
            'company_id' => 'required|exists:companies',
            'review_text' => 'required',
        ]);

        if ($data->fails()) {
            return response()->json([
                'success' => false,
                "errors" => $data->errors()
            ])->header('Status-Code', 200);
        }

        $review = new Review();
        $review->writer_id = Auth::id();
        $review->company_id = $data->validated()['company_id'];
        $review->review_text = $data->validated()['review_text'];
        $review->save();

        return response()->json(['success' => true, 'message' => 'Review created successfully']);
    }
}

<?php
namespace App\Http\Controllers\Frontend;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController{
    public function addReview(Request $request){
        $data = [
            'user_id' => Auth::id(),
            'course_id' => $request->course_id,
            'rating' => $request->rating,
            'comment' => $request->review
        ];
        Review::create($data);
    }

    public function delete($id){
        Review::find($id)->delete();
        return back();
    }
}

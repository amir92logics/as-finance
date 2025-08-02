<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Rating;
use App\Models\RatingComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $purifiedData = $request->all();

        $rules = [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|numeric|min:1|max:5',
            'massage' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|max:100'
        ];

        $validate = Validator::make($purifiedData, $rules);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        $userId = auth()->id();
        $ProductId = $request->product_id;

        $existingRating = Rating::firstWhere([
            'user_id' => $userId,
            'product_id' => $ProductId,
        ]);
        if ($existingRating) {
            return back()->with('error', 'You have already rated this item');
        }

        $isBuyItem = Order::where('user_id', $userId)
            ->whereHas('orderItem', function ($query) use ($ProductId) {
                $query->where('product_id', $ProductId);
            })
            ->first();

        if (!$isBuyItem) {
            return back()->with('error', 'You have to purchase this item for rating');
        }

        $rating = new Rating();
        $rating->user_id = $userId;
        $rating->rating = $request->rating;
        $rating->product_id = $ProductId;
        $rating->save();
        $ratingComment = new RatingComment();
        $ratingComment->rating_id = $rating->id;
        $ratingComment->comment = $request->massage;
        $ratingComment->name = $request->name;
        $ratingComment->email = $request->email;
        $ratingComment->save();

        return back()->with('success', 'Your Rating Added successfully');
    }
}

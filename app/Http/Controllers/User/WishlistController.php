<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Wishlist::where('user_id',auth()->user()->id)->orderBy('created_at','desc')->paginate(12);
        return view(template().'user.pages.wishlist',compact('wishlists'));
    }
    public function addToWishlist(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products,id',
        ]);
        $id = $request->id;
        $wishlist = Wishlist::where('user_id',auth()->user()->id)->where('product_id',$id)->first();
        if($wishlist){
            $wishlist->delete();
            return 1;
        }else{
            $wishlist = new Wishlist();
            $wishlist->user_id = auth()->user()->id;
            $wishlist->product_id = $id;
            $wishlist->save();
            return 2;
        }

    }

}

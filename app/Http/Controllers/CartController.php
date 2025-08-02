<?php

namespace App\Http\Controllers;

use App\Models\ContentDetails;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => ['required', Rule::exists('products', 'id')],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::where('id',$request->product_id)->first();
        if (!$product){
            return response()->json(['message' => 'Product not found'], 404);
        }
        $id = $request->product_id;
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $request->quantity ?? 1;
        }else{
            $cart[$id] = [
                "id" => $id,
                "image" => getFile($product->driver, $product->thumbnail_image),
                "name" => optional($product->details)->title,
                "quantity" => $request->quantity+0 ?? 1,
                'quantity_unit' => $product->quantity_unit,
                'product_quantity' => $product->quantity+0,
                "price" => $product->price+0,

            ];
        }

        session()->put('cart', $cart);


        if (session('coupon')){
            $discount = discountPrice(cartTotal($cart),session('coupon'));
        }

        return response()->json([
            'success' => 'Item added successfully to the cart',
            'data' => $cart,
            'discount' => isset($discount)?$discount:null,
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => ['required', Rule::exists('products', 'id')],
        ]);
        $cart = session()->get('cart');
        if (isset($cart[$request->product_id])) {
            unset($cart[$request->product_id]);
            session()->put('cart', $cart);
            if (session('coupon')){
                $discount = discountPrice(cartTotal($cart),session('coupon'));
            }
        }

        return response()->json([
            'success' => 'Card Remove successfully',
            'data' => $cart,
            'discount' => isset($discount)?$discount:null,
        ]);
    }


    public function updateQuantity(Request $request)
    {
        $request->validate([
            'quantity' => ['required'],
            'product_id' => ['required', Rule::exists('products', 'id')],
        ]);
        $productId = $request->product_id;
        $quantity = $request->quantity;
        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            if ($quantity > 0) {
                $cart[$productId]['quantity'] = $quantity;
            } else {
                unset($cart[$productId]);
            }
        }
        session()->put('cart', $cart);

        if (session('coupon')){
            $discount = discountPrice(cartTotal($cart),session('coupon'));
        }
        return response()->json([
            'success' => 'Cart updated successfully',
            'data' => $cart,
            'discount' => isset($discount)?$discount:null,
        ]);
    }

public function cart()
{

    if (!basicControl()->ecommerce){
        abort(403);
    }

    if (count(session('cart')??[]) <= 0){
        return redirect()->route('page')->with('error','Cart is empty');
    }

    $content = ContentDetails::with('content')
                ->whereHas('content', function($query){
                    $query->where('name', 'cart_section');
                })->get();
    $single_content = $content->where('content.name', 'cart_section')->where('content.type', 'single')->first();
    $data['cart_section'] = [
        'single' => $single_content? collect($single_content->description ?? [])->merge($single_content->content->only('media')) : [],
    ];

    $pageSeo = Page::where('slug', 'view_cart')->first();
    $pageSeo->meta_keywords = isset($pageSeo->meta_keywords) && $pageSeo->meta_keywords != null?  implode(",", $pageSeo->meta_keywords):'';
    $pageSeo->image = getFile($pageSeo->meta_image_driver, $pageSeo->meta_image);
    return view(template().'pages.cart_view',compact('pageSeo'),$data);
}




}

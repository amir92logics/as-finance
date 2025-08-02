<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductDetails;
use App\Models\Rating;
use App\Models\RatingComment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use ApiResponse;
    public function products(Request $request)
    {
        if (!basicControl()->ecommerce){
            return $this->jsonError('Access Forbidden');
        }
        $pageSeo = Page::where('slug', 'products')->first();
        $pageSeo->meta_keywords = isset($pageSeo->meta_keywords) && $pageSeo->meta_keywords?  implode(",", $pageSeo->meta_keywords):'';
        $pageSeo->image = getFile($pageSeo->meta_image_driver, $pageSeo->meta_image);
        $products = Product::with('details','wishlist','reviews.comment')
            ->where('is_published', 1)
            ->when($request->has('status') && $request->status, function ($query) use ($request) {
                $query->whereIn('status', $request->input('status'));
            })
            ->when($request->has('category') && $request->category,function($query) use ($request){
                $query->whereIn('category_id', $request->input('category'));
            })
            ->when($request->has('min') && $request->min, function ($query) use ($request) {
                $query->where('price', '>=', $request->input('min'));
            })
            ->when($request->has('max')  && $request->max, function ($query) use ($request) {
                $query->where('price', '<=', $request->input('max'));
            })
            ->when($request->has('sorting') && $request->sorting == 'best_selling',function ($query){
                $query->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                    ->select('products.*', DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sold'))
                    ->groupBy('products.id')
                    ->orderByDesc('total_sold');
            })
            ->when($request->has('sorting') && $request->sorting == 'asc' || $request->sorting == 'desc',function ($query){
                $query->orderBy(
                    ProductDetails::select('title')
                        ->whereColumn('product_details.product_id', 'products.id')
                        ->limit(1),
                    request()->sorting
                );
            })
            ->when(request()->has('sorting') && $request->sorting == 'low_to_high', function ($query) {
                $query->orderBy('price', 'asc');
            })
            ->paginate(9);
        $data['categories'] = $categories = Category::where('status',1)->get()->map(function($category){
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image' => getFile($category->category_image_driver,$category->category_image),
            ];
        });
        $data['products'] = ProductResource::collection($products);
            return $this->jsonSuccess($data);
    }

    public function productDetails($id)
    {
        $product = Product::with('details','wishlist','reviews.comment')->where('id', $id)->first();
        if (!$product){
            return $this->jsonError('Product not found');
        }

        return $this->jsonSuccess(new ProductResource($product));
    }

    public function addRating(Request $request)
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
            return $this->jsonError(collect($validate->errors())->collapse());
        }

        $userId = auth()->id();
        $ProductId = $request->product_id;

        $existingRating = Rating::firstWhere([
            'user_id' => $userId,
            'product_id' => $ProductId,
        ]);
        if ($existingRating) {
            return $this->jsonError('You have already rated this item');
        }

        $isBuyItem = Order::where('user_id', $userId)
            ->whereHas('orderItem', function ($query) use ($ProductId) {
                $query->where('product_id', $ProductId);
            })
            ->first();

        if (!$isBuyItem) {
            return $this->jsonError('You have to purchase this item for rating');
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

        return $this->jsonSuccess('Your Rating Added successfully');
    }
}

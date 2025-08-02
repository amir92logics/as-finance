<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\LanguageResource;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\TransactionResouce;
use App\Http\Resources\UserResource;
use App\Http\Resources\WishlistResource;
use App\Models\Area;
use App\Models\Coupon;
use App\Models\FireBaseToken;
use App\Models\Language;
use App\Models\NotificationPermission;
use App\Models\NotificationTemplate;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WishList;
use App\Rules\PhoneLength;
use App\Traits\ApiResponse;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponse,Upload;

    public function profile()
    {
        $languages = Language::where('status',1)->get();
        $data = [
            'languages' => LanguageResource::collection($languages),
            'profile' => new UserResource(Auth::user())
        ];

        return $this->jsonSuccess($data);
    }

    public function updateProfile(Request $request)
    {
        $languages = Language::all()->map(function ($item) {
            return $item->id;
        });
        $user = Auth::user();
        $phoneCode = $request->phone_code;
        $rules = [
            'firstname' => ['required'],
            'lastname' => ['required'],
            'email' => ['required',Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['required',Rule::unique('users', 'username')->ignore($user->id)],
            'address' => ['nullable'],
            'phone' => ['required', 'string', new PhoneLength($phoneCode),Rule::unique('users', 'phone')->ignore($user->id)],
            'phone_code' => 'required | max:15',
            'country_code' => 'required | string | max:80',
            'country' => 'required | string | max:80',
            'language' => Rule::in($languages),
            'image' => ['nullable','max:3072','image','mimes:jpg,jpeg,png']
        ];

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json($this->withError(collect($validation->errors())->collapse()));
        }

        if ($request->hasFile('image')) {
            $image = $this->fileUpload($request->image, config('filelocation.userProfile.path'), null, null, 'avif', 60, $user->image, $user->image_driver);
            if ($image) {
                $profileImage = $image['path'];
                $ImageDriver = $image['driver'];
            }
        }

        $user->firstname =  $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->address_one =  $request->address;
        $user->phone = $request->phone;
        $user->phone_code = $request->phone_code;
        $user->country = $request->country;
        $user->country_code = $request->country_code;
        $user->language_id =  $request->language;
        $user->image = $profileImage ?? $user->image;
        $user->image_driver = $ImageDriver ?? $user->image_driver;
        $user->save();

        return response()->json($this->withSuccess('Profile updated successfully.'));

    }

    public function changePassword(Request $request){
        $rules = [
            'current_password' => "required",
            'password' => "required|min:5|confirmed",
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($this->withError(collect($validator->errors())->collapse()));
        }
        $user = Auth::user();
        try {
            if (Hash::check($request->current_password, $user->password)) {
                $user->password = bcrypt($request->password);
                $user->save();
                return response()->json($this->withSuccess('Password updated successfully.'));
            } else {
                throw new \Exception('Current password did not match');
            }
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function transactions(Request $request)
    {

        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $transactions = Transaction::where('user_id', Auth::id())
            ->when($request->trx_id , function ($query) use ($request){
                $query->where('trx_id',$request->trx_id);
            })
            ->when($request->remark , function ($query) use ($request){
                $query->where('remarks','LIKE','%'.$request->remark.'%');
            })
            ->when($fromDate && $toDate , function ($query) use ($fromDate ,$toDate){
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            })
            ->select('id','amount','charge','trx_type','trx_id','remarks','created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $data['data'] = TransactionResouce::collection($transactions);
        return $this->jsonSuccess($data);

    }

    public function firebaseTokenSave(Request $request)
    {
        if (!$request->fcm_token) {
            return response()->json($this->withError('FCM Token is required'));
        }

        try {
            $user = auth()->user();
            FireBaseToken::firstOrCreate(
                [
                    'token' => $request->fcm_token
                ],
                [
                    'tokenable_id' => $user->id,
                    'tokenable_type' => User::class,
                ]
            );
            return response()->json($this->withSuccess('FCM Token saved'));
        } catch (\Exception $exception) {
            return response()->json($this->withError($exception->getMessage()));
        }
    }

    public function template()
    {
        try {
            $user = User::with('notifypermission')->findOrFail(Auth::id());
            $allTemplates = NotificationTemplate::where('notify_for', 0)
                ->get();
            $data['templates'] = $allTemplates;
            $data['user_permission'] = optional($user)->notifypermission;
            return $this->jsonSuccess($data);
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function updateNotificationPermission(Request $request)
    {
        $rules = [
            'email_key' => ['required'],
            'in_app_key' => ['required'],
            'push_key' => ['required'],
            'sms_key' => ['required']

        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json($this->withError(collect($validation->errors())->collapse()));
        }
        try {
            $user = Auth::user();
            $userTemplate = NotificationPermission::where('notifyable_id', $user->id)
                ->where('notifyable_type', User::class)
                ->first();
            if ($userTemplate) {
                $userTemplate->template_email_key = $request->email_key;
                $userTemplate->template_in_app_key = $request->in_app_key;
                $userTemplate->template_push_key = $request->push_key;
                $userTemplate->template_sms_key = $request->sms_key;
                $userTemplate->save();
            }else{
                NotificationPermission::create([
                    'notifyable_id' => $user->id,
                    'notifyable_type' => User::class,
                    'template_email_key' => $request->email_key,
                    'template_in_app_key' => $request->in_app_key,
                    'template_push_key' => $request->push_key,
                    'template_sms_key' => $request->sms_key,

                ]);
            }

            return response()->json($this->withSuccess('Notification permission updated successfully.'));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }


    public function language(Request $request)
    {
        try {
            if (!$request->id) {
                $data['languages'] = Language::select(['id', 'name', 'short_name'])->where('status', 1)->get();
                return response()->json($this->withSuccess($data));
            }
            $lang = Language::where('status', 1)->find($request->id);
            if (!$lang) {
                return response()->json($this->withError('Record not found'));
            }

            $json = file_get_contents(resource_path('lang/') . $lang->short_name . '.json');
            if (empty($json)) {
                return response()->json($this->withError('File Not Found.'));
            }

            $json = json_decode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return response()->json($this->withSuccess($json));

        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function addToWishlist(Request $request)
    {
        $rule = [
            'id' => 'required|exists:products,id',
        ];

        $validation = Validator::make($request->all(), $rule);
        if ($validation->fails()) {
            return response()->json($this->withError(collect($validation->errors())->collapse()));
        }
        $id = $request->id;
        $wishlist = Wishlist::where('user_id',auth()->user()->id)->where('product_id',$id)->first();
        if($wishlist){
            $wishlist->delete();
            return $this->jsonSuccess('Wishlist removed successfully.');
        }else{
            $wishlist = new Wishlist();
            $wishlist->user_id = auth()->user()->id;
            $wishlist->product_id = $id;
            $wishlist->save();
            return $this->jsonSuccess('Wishlist added successfully.');
        }

    }

    public function wishlist()
    {
        $wishlists = Wishlist::query()
            ->with('product.details')
            ->where('user_id', Auth::id())
            ->paginate(15);

        $wishlists->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_image' => getFile(optional($item->product)->driver, optional($item->product)->thumbnail_image),
                'product_name' => optional(optional($item->product)->details)->title,
                'price' => currencyPosition(optional($item->product)->price),
                'avg_rating' => optional($item->product)->averageRating,
            ];
        });

        return $this->jsonSuccess($wishlists);
    }

    public function orders()
    {
        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(15);

       return $this->jsonSuccess(OrderResource::collection($orders));
    }

    public function orderDetails($id)
    {
        $order = Order::where('id', $id)->first();
        if (!$order){
            return response()->json($this->withError('Order not found'));
        }
        $order = [
            'order' => new OrderResource($order),
            'order_items' => OrderItemResource::collection($order->orderItem),
        ];
        return $this->jsonSuccess($order);
    }

    public function area()
    {
        $areas = Area::with('shippingCharge')->get();
        return $this->jsonSuccess($areas);
    }


    public function applyCoupon(Request $request)
    {
        $auth = auth()->user();

        if (!$auth) {
            return $this->jsonError('You have to authorize for applying a coupon.');
        }

        $rules = [
            'coupon' => 'required|exists:coupons,coupon_code',
        ];

        $req = $request->all();
        $validator = Validator::make($req, $rules);


        if ($validator->fails()) {
            return $this->jsonError(collect($validator->errors())->collapse());
        }
        $couponCode = $req['coupon'];
        $coupon = Coupon::where('coupon_code', $couponCode)->first();

        $cart =  $request->cart_items;


        $price = cartTotal($cart);

        if($coupon->applicableProducts->first()){
            $cartItems = collect($cart)->keys()->toArray();
            $applicableProducts = $coupon->applicableProducts->pluck('product_id')->toArray();
            $matchedItems = array_intersect($cartItems, $applicableProducts);
            if (empty($matchedItems)) {
                return $this->jsonError('You cannot apply this coupon for this order items');
            }
        }

        $orderCount = Order::where('coupon_code', $couponCode)
            ->where('user_id', $auth->id)
            ->count();

        if ($orderCount >= $coupon->maximum_order) {
            return $this->jsonError('Coupon usage limit reached.');
        }

        if ($coupon->start_date > now()) {
            return $this->jsonError('Coupon has not started yet.');
        }

        if ($coupon->end_date < now()) {
            return $this->jsonError('Coupon has expired.');
        }

        if ($coupon->minimum_order_price > $price) {
            $minimumPrice = $coupon->minimum_order_price;
            return $this->jsonError("You have to purchase more then $minimumPrice for apply this coupon.");
        }

        $discount = apiDiscountPrice($price,$req['coupon']);

        return $this->jsonSuccess($discount);

    }
}

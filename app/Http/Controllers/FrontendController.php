<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\InvestmentPlan;
use App\Models\Language;
use App\Models\Order;
use App\Models\Page;
use App\Models\PageDetail;
use App\Models\Product;
use App\Models\ProductDetails;
use App\Models\Project;
use App\Models\Rating;
use App\Models\Transaction;
use App\Traits\Frontend;
use App\Traits\Notify;
use App\Traits\PaymentValidationCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FrontendController extends Controller
{
    use Frontend, Notify, PaymentValidationCheck;

    public function page($slug = '/')
    {

        try {
            $selectedTheme = basicControl()->theme ?? 'light';
            $existingSlugs = collect([]);
            DB::table('pages')->select('slug')->get()->map(function ($item) use ($existingSlugs) {
                $existingSlugs->push($item->slug);
            });

            if (!in_array($slug, $existingSlugs->toArray())) {
                throw new \Exception('Page not found', 404);
            }

            $pageDetails = PageDetail::with('page')
                ->whereHas('page', function ($query) use ($slug, $selectedTheme) {
                    $query->where(['slug' => $slug, 'template_name' => $selectedTheme]);
                })
                ->first();

            $pageSeo = Page::where('slug', $slug)->first();
            if ($pageSeo->meta_keywords) {
                $pageSeo->meta_keywords = implode(",", $pageSeo->meta_keywords);
            }


            $pageSeo->image = getFile($pageSeo->meta_image_driver, $pageSeo->meta_image);

            $sectionsData = $this->getSectionsData($pageDetails->sections, $pageDetails->content, $selectedTheme);

            return view("themes.{$selectedTheme}.page", compact('sectionsData', 'pageSeo'));

        } catch (\Exception $exception) {
            \Cache::forget('ConfigureSetting');
            if ($exception->getCode() == 404) {
                abort(404);
            }
            if ($exception->getCode() == 403) {
                abort(403);
            }
            if ($exception->getCode() == 401) {
                abort(401);
            }

            if ($exception->getCode() == 503) {
                return redirect()->route('maintenance');
            }
            if ($exception->getCode() == 1049) {
                die('Unable to establish a connection to the database. Please check your connection settings and try again later');
            }
            return redirect()->route('instructionPage');
        }

    }


    public function applyCoupon(Request $request)
    {
        $auth = auth()->user();

        if (!$auth) {
            return response(['errors' => ['coupon' => ['You have to authorize for applying a coupon.']]], 400);
        }

        $rules = [
            'coupon' => 'required|exists:coupons,coupon_code',
        ];

        $req = $request->all();
        $validator = Validator::make($req, $rules);


        if ($validator->fails()) {
            return response(['errors' => $validator->messages()], 400);
        }
        $couponCode = $req['coupon'];
        $coupon = Coupon::where('coupon_code', $couponCode)->first();

        $price = cartTotal(session('cart') ?? []);

        if (count(session('cart') ?? []) <= 0) {
            return response()->json(['errors' => ['coupon' => ['Please add to cart and try again.']]], 400);
        }


        if ($coupon->applicableProducts->first()) {
            $cartItems = collect(session('cart') ?? [])->keys()->toArray();
            $applicableProducts = $coupon->applicableProducts->pluck('product_id')->toArray();
            $matchedItems = array_intersect($cartItems, $applicableProducts);
            if (empty($matchedItems)) {
                return response()->json(['errors' => ['coupon' => ["You cannot apply this coupon for this order items"]]], 400);
            }
        }

        $orderCount = Order::where('coupon_code', $couponCode)
            ->where('user_id', $auth->id)
            ->count();

        if ($orderCount >= $coupon->maximum_order) {
            return response(['errors' => ['coupon' => ['Coupon usage limit reached.']]], 400);
        }

        if ($coupon->start_date > now()) {
            return response(['errors' => ['coupon' => ['Coupon has not started yet.']]], 400);
        }

        if ($coupon->end_date < now()) {
            return response(['errors' => ['coupon' => ['Coupon has expired.']]], 400);
        }

        if ($coupon->minimum_order_price > $price) {
            $minimumPrice = $coupon->minimum_order_price;
            return response(['errors' => ['coupon' => ["You have to purchase more then $minimumPrice for apply this coupon."]]], 400);
        }

        $discount = discountPrice($price, $req['coupon']);

        return response()->json($discount);

    }

    public function sentContactInfo(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required | numeric',
            'email' => 'required | email',
            'message' => 'required',
            'invest_type' => 'nullable'
        ]);
        $params = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'invest_type' => $request->invest_type,
            'message' => $request->message
        ];
        $actionAdmin = [
            "name" => $request->name,
            "image" => Auth::user() ? getFile(Auth::user()->image_driver, Auth::user()->image) : getFile('driver', 'image'),
            "link" => '#',
            "icon" => "fas fa-ticket-alt text-white"
        ];
        $firebaseAction = '#';
        $this->adminMail('CONTACT_INFO', $params);
        $this->adminPushNotification('CONTACT_INFO', $params, $actionAdmin);
        $this->adminFirebasePushNotification('CONTACT_INFO', $params, $firebaseAction);

        return redirect()->back()->with('success', 'Your information has been sent.');
    }


    public function language($locale)
    {
        app()->setLocale($locale);
        $lang = Language::where('short_name', $locale)->first();
        session()->put('lang', $locale);
        session()->put('language', $lang);
        return redirect()->route('page');
    }

    public function projects()
    {
        $projects = Project::with('details')
            ->where(function ($query) {
                $query->where('expiry_date', '>', Carbon::now())
                    ->orWhere('project_duration_has_unlimited', 1);
            })
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view(template() . 'user.invests.index', compact('invests'));
    }

    public function plans()
    {
        $plans = InvestmentPlan::where(['status' => 1, 'soft_delete' => 0])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view(template() . 'user.plan.index', compact('plans'));
    }

    public function products()
    {
        if (!basicControl()->ecommerce) {
            abort(403);
        }
        $pageSeo = Page::where('slug', 'products')->first();
        $pageSeo->meta_keywords = isset($pageSeo->meta_keywords) && $pageSeo->meta_keywords ? implode(",", $pageSeo->meta_keywords) : '';
        $pageSeo->image = getFile($pageSeo->meta_image_driver, $pageSeo->meta_image);
        $products = Product::with(['details', 'wishlist', 'reviews'])
            ->where('is_published', 1)
            ->orderByDesc('created_at')
            ->when(\request()->has('status') && \request()->status, function ($query) {
                $query->whereIn('status', \request()->input('status'));
            })
            ->when(\request()->has('category') && \request()->category, function ($query) {
                $query->whereIn('category_id', \request()->input('category'));
            })
            ->when(\request()->has('min') && \request()->min, function ($query) {
                $query->where('price', '>=', \request()->input('min'));
            })
            ->when(\request()->has('max') && \request()->max, function ($query) {
                $query->where('price', '<=', \request()->input('max'));
            })
            ->when(\request()->has('sorting') && \request()->sorting == 'best_selling', function ($query) {
                $query->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                    ->select('products.*', DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sold'))
                    ->groupBy('products.id')
                    ->orderByDesc('total_sold');
            })
            ->when(\request()->has('sorting') && \request()->sorting == 'asc' || \request()->sorting == 'desc', function ($query) {
                $query->orderBy(
                    ProductDetails::select('title')
                        ->whereColumn('product_details.product_id', 'products.id')
                        ->limit(1),
                    request()->sorting
                );
            })
            ->when(request()->has('sorting') && \request()->sorting == 'low_to_high', function ($query) {
                $query->orderBy('price', 'asc');
            })
            ->paginate(9);
        $categories = Category::where('status', 1)->get();
        return view(template() . 'pages.products', compact('products', 'pageSeo', 'categories'));
    }

    public function productDetails($slug)
    {
        $pageSeo = Page::where('slug', 'product_details')->first();
        $pageSeo->meta_keywords = isset($pageSeo->meta_keywords) && $pageSeo->meta_keywords != null ? implode(",", $pageSeo->meta_keywords) : '';
        $pageSeo->image = getFile($pageSeo->meta_image_driver, $pageSeo->meta_image);
        $product = Product::with(['details', 'reviews.comment'])->whereHas('details', function ($query) use ($slug) {
            $query->where('slug', $slug);
        })->firstOrFail();
        $data['reviews'] = Rating::where('product_id', $product->id)->get();
        foreach ($data['reviews'] as $review) {
            $rating = $review->rating;
            $fullStars = floor($rating);
            $hasHalfStar = ($rating - $fullStars) >= 0.5;
            $stars = '';
            for ($i = 0; $i < $fullStars; $i++) {
                $stars .= '<li> <i class="fas fa-star"></i></li>';
            }
            if ($hasHalfStar) {
                $stars .= '<li><i class="fas fa-star-half-alt"></i></li>';
            }
            for ($i = 0; $i < 5 - $fullStars - ($hasHalfStar ? 1 : 0); $i++) {
                $stars .= '<li><i class="far fa-star"></i></li>';
            }

            $review->stars = $stars;
        }
        $rating = $product->reviews->avg('rating');
        $fullStars = floor($rating);
        $hasHalfStar = ($rating - $fullStars) >= 0.5;
        $stars = '';
        for ($i = 0; $i < $fullStars; $i++) {
            $stars .= ' <li><i class="fas fa-star"></i></li>';
        }
        if ($hasHalfStar) {
            $stars .= '<li><i class="fas fa-star-half-alt"></i></li>';
        }
        for ($i = 0; $i < 5 - $fullStars - ($hasHalfStar ? 1 : 0); $i++) {
            $stars .= '<li><i class="far fa-star"></i></li>';
        }
        $data['avg_rating'] = $stars;

        $relatedProducts = Product::with('details')->where('category_id', $product->category_id)->where('id', '!=', $product->id)->limit(12)->get();
        return view(template() . 'pages.product_details', compact('product', 'pageSeo', 'relatedProducts'), $data);

    }


    public function checkout()
    {
        if (!basicControl()->ecommerce) {
            abort(403);
        }
        if (count(session('cart') ?? []) <= 0) {
            return redirect()->route('page')->with('error', 'Cart is empty');
        }
        $pageSeo = Page::where('slug', 'checkout')->first();
        $pageSeo->meta_keywords = isset($pageSeo->meta_keywords) && $pageSeo->meta_keywords ? implode(",", $pageSeo->meta_keywords) : '';
        $pageSeo->image = getFile($pageSeo->meta_image_driver, $pageSeo->meta_image);

        $areas = Area::get();
        return view(template() . 'pages.checkout', compact('pageSeo', 'areas'));
    }

    public function shippingCharge(Request $request)
    {
        $area_id = $request->area_id;
        $area = Area::with('shippingCharge')->where('id', $area_id)->first();
        if (!$area) {
            return response()->json(['errors' => ['charge' => ['Area not found']]], 400);
        }
        $totalDeliveryCharge = deliveryCharge($area);
        return response()->json(['charge' => $totalDeliveryCharge]);

    }


    public function order(Request $request)
    {
        if (!basicControl()->ecommerce) {
            abort(403);
        }
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'email' => 'required|email',
            'city' => 'required',
            'zip' => 'nullable|numeric',
            'payment_method' => 'required|in:cash,checkout,wallet',
            'additional_information' => 'nullable|max:1000',
            'area' => 'required | exists:areas,id',
        ]);

        $area = Area::with('shippingCharge')->findOrFail($request->area);
        $vat = vat(cartTotal(session('cart' ?? [])));
        $subtotal = cartTotal(session('cart' ?? []));
        $deliveryCharge = deliveryCharge($area);

        if (session('coupon')) {
            $discount = discountPrice(cartTotal(session('cart' ?? [])), session('coupon'))['discountWithOutCurrency'];
        }

        if (isset($discount)) {
            $total = ($subtotal + $vat + $deliveryCharge) - $discount;
        } else {
            $total = $subtotal + $vat + $deliveryCharge;
        }

        DB::beginTransaction();
        try {
            $order = new Order();
            $order->user_id = \auth()->id() ?? null;
            if ($request->payment_method === 'cash') {
                $order->gateway_id = 2000;
            } elseif ($request->payment_method === 'wallet') {
                if (!auth()->user()) {
                    throw new \Exception('You need to login first');
                }
                $order->gateway_id = 2100;
            }
            $order->first_name = $request->first_name;
            $order->last_name = $request->last_name;
            $order->email = $request->email;
            $order->phone = $request->phone;
            $order->city = $request->city;
            $order->zip = $request->zip;
            $order->address = $request->address;
            $order->area_id = $request->area;
            $order->additional_information = $request->additional_information;
            if (session('coupon')) {
                $discount = discountPrice(cartTotal(session('cart' ?? [])), session('coupon'))['discountWithOutCurrency'];
                $order->coupon_code = session('coupon');
                $order->discount = $discount;
            }
            $order->subtotal = $subtotal;
            $order->delivery_charge = $deliveryCharge;
            $order->vat = $vat;
            $order->total = $total;
            $order->save();

            $cart = session()->get('cart', []);
            $itemsData = [];
            foreach ($cart as $item) {
                $itemsData[] = [
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ];
            }
            $order->orderItem()->createMany($itemsData);

            session()->forget('cart');
            session()->forget('coupon');
            session()->forget('discountPrice');
            session()->forget('discount');

            if ($request->payment_method == 'checkout') {
                session()->put('order_id', $order->id);
                session()->put('total', $total);
                DB::commit();
                return redirect()->route('payment');
            }

            if ($request->payment_method === 'cash') {
                $deposit = Deposit::create([
                    'user_id' => Auth::user() ? Auth::user()->id : 0,
                    'payment_method_id' => 2000,
                    'payment_method_currency' => basicControl()->base_currency,
                    'amount' => $order->total,
                    'percentage_charge' => 0,
                    'fixed_charge' => 0,
                    'payable_amount' => $order->total,
                    'base_currency_charge' => 0,
                    'payable_amount_in_base_currency' => $order->total,
                    'status' => 2,
                ]);
                $order->depositable()->save($deposit);

                $order->transaction_id = $deposit->trx_id;
                $order->save();
            } elseif ($request->payment_method === 'wallet') {
                if (Auth::user()) {
                    $user = Auth::user();
                    $amount = $order->total;
                    if ($amount <= $user->balance) {
                        $user->balance = $user->balance - $amount;
                        $user->save();
                        $transaction = new Transaction();
                        $transaction->user_id = Auth::user()->id;
                        $transaction->amount = $amount;
                        $transaction->charge = 0;
                        $transaction->trx_type = '+';
                        $transaction->remarks = 'Payment Via Wallet';
                        $order->transactional()->save($transaction);
                        $order->transaction_id = $transaction->trx_id;
                        $order->payment_status = 1;
                        $order->save();
                    } else {
                        throw new \Exception('Insufficient balance');
                    }
                } else {
                    throw new \Exception('You need to login first');
                }
            }
            $user = auth()->user();
            $params = [
                'order_number' => $order->order_number,
                'trx_id' => $order->transaction_id,
                'username' => $user->username ?? 'Guest User',
                'payment_amount' => currencyPosition($order->total),
                'payment_method' => $request->payment_method == 'cash' ? 'Cash on Delivery' : 'Wallet',
                'datetime' => dateTime($order->created_at),
            ];

            $action = [
                "link" => "#",
                "icon" => "fa-regular fa-bell"
            ];

            $firebaseAction = "#";

            if ($user) {
                $this->sendMailSms($user, 'PAYMENT_CONFIRMATION', $params);
                $this->userPushNotification($user, 'PAYMENT_CONFIRMATION', $params, $action);
                $this->userFirebasePushNotification($user, 'PAYMENT_CONFIRMATION', $params, $firebaseAction);
            }

            if ($user) {
                $actionAdmin = [
                    "name" => $user->firstname . ' ' . $user->lastname,
                    "image" => getFile($user->image_driver, $user->image),
                    "link" => route('admin.transaction'),
                    "icon" => "fa-regular fa-bell"
                ];
            } else {
                $actionAdmin = [
                    "name" => 'Guest User',
                    "image" => getFile('local', 'image.avif'),
                    "link" => route('admin.transaction'),
                    "icon" => "fa-regular fa-bell"
                ];
            }

            $firebaseAction = route('admin.transaction');
            $this->adminMail('PAYMENT_CONFIRMATION_ADMIN', $params);
            $this->adminPushNotification('PAYMENT_CONFIRMATION_ADMIN', $params, $actionAdmin);
            $this->adminFirebasePushNotification('PAYMENT_CONFIRMATION_ADMIN', $params, $firebaseAction);
            DB::commit();
            return redirect()->route('success', $order->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function payment()
    {
        $data['gateways'] = Gateway::where('status', 1)->orderBy('sort_by', 'ASC')->get();
        $data['amount'] = session('total');
        return view(template() . 'pages.order_payment', $data);
    }

    public function orderComplete($id)
    {
        $pageSeo = Page::where('slug', 'order_complete')->first();
        $pageSeo->meta_keywords = isset($pageSeo->meta_keywords) && $pageSeo->meta_keywords ? implode(",", $pageSeo->meta_keywords) : '';
        $pageSeo->image = getFile($pageSeo->meta_image_driver, $pageSeo->meta_image);
        $order = Order::findOrFail($id);
        return view(template() . 'pages.order_completed', compact('order', 'pageSeo'));
    }


    public function orderPaymentRequest(Request $request)
    {
        $id = session()->get('order_id');
        $amount = session('total');
        $gateway = $request->gateway_id;
        $currency = $request->supported_currency ?? '';
        $cryptoCurrency = $request->supported_crypto_currency;

        $order = Order::findOrFail($id);

        try {
            $checkAmountValidate = $this->validationCheck($amount, $gateway, $currency, $cryptoCurrency, 'order');
            if ($checkAmountValidate['status'] == 'error') {
                return back()->with('error', $checkAmountValidate['msg']);
            }
            $deposit = Deposit::create([
                'user_id' => Auth::id() ?? null,
                'payment_method_id' => $checkAmountValidate['data']['gateway_id'],
                'payment_method_currency' => $checkAmountValidate['data']['currency'],
                'amount' => $checkAmountValidate['data']['amount'],
                'depositable_id' => $id,
                'depositable_type' => 'App\Models\Order',
                'percentage_charge' => $checkAmountValidate['data']['percentage_charge'],
                'fixed_charge' => $checkAmountValidate['data']['fixed_charge'],
                'payable_amount' => $checkAmountValidate['data']['payable_amount'],
                'base_currency_charge' => $checkAmountValidate['data']['base_currency_charge'],
                'payable_amount_in_base_currency' => $checkAmountValidate['data']['payable_amount_base_in_currency'],
                'status' => 0,
            ]);

            $order->depositable()->save($deposit);
            session()->forget('total');
            session()->forget('order_id');
            return redirect(route('payment.process', $deposit->trx_id));
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}

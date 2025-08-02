<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Traits\ApiResponse;
use App\Traits\Notify;
use App\Traits\PaymentValidationCheck;
use hisorange\BrowserDetect\Exceptions\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use ApiResponse,Notify,PaymentValidationCheck;
    public function order(Request $request)
    {
        if (!basicControl()->ecommerce){
            return $this->jsonError('Ecommerce feature is not available');
        }

        $rules = [
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
            'cart_items' => 'required|array',
            'coupon_code' => 'nullable|exists:coupons,code',
        ];

        $validator  = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->jsonError(collect($validator->errors())->collapse());
        }

        $cart = [];
        foreach ($request->cart_items as $item) {
            $product = Product::where('id', $item['id']??null)->first();
            if (!$product){
                return $this->jsonError('Product not found');
            }
            $cartItem = [
                'id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ];

            $cart[] = $cartItem;
        }



        $area = Area::with('shippingCharge')->where('id',$request->area)->first();
        if (!$area){
            return $this->jsonError('Area not found');
        }

        $vat = vat(cartTotal($cart));

        $subtotal = cartTotal($cart);

        $deliveryCharge = deliveryCharge($area,$cart);


        if ($request->has('coupon_code') && $request->coupon_code) {
            $discount = discountPrice(cartTotal($cart),$request->coupon_code)['discountWithOutCurrency'];
        }

        if (isset($discount)){
            $total = ($subtotal + $vat + $deliveryCharge) - $discount;
        }else{
            $total = $subtotal + $vat + $deliveryCharge;
        }

        DB::beginTransaction();
        try {
            $order = new Order();
            $order->user_id = \auth()->id()??null;
            if ($request->payment_method === 'cash'){
                $order->gateway_id = 2000;
            }elseif ($request->payment_method === 'wallet'){
                if (!auth()->user()){
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
            $order->area_id  = $request->area ;
            $order->additional_information  = $request->additional_information ;
            if ($request->has('coupon_code') && $request->coupon_code){
                $discount = discountPrice(cartTotal($cart),$request->coupon_code)['discountWithOutCurrency'];
                $order->coupon_code  = $request->coupon_code ;
                $order->discount  = $discount ;
            }
            $order->subtotal = $subtotal;
            $order->delivery_charge = $deliveryCharge;
            $order->vat = $vat;
            $order->total = $total;
            $order->save();

            $itemsData = [];
            foreach ($cart as $item) {
                $product = Product::where('id', $item['id']??null)->first();
                if (!$product){
                    return $this->jsonError('Product not found');
                }
                $itemsData[] = [
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity']??1,
                    'price' => $product->price,
                ];
            }
            $order->orderItem()->createMany($itemsData);
            DB::commit();
            return response()->json($this->withSuccess(['order_id'=>$order->id],'Order created successfully.'));
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function makePayment(Request $request)
    {
        $rules = [
            'order_id' => 'required',
            'gateway_id' => 'required',
            'supported_currency' => 'nullable',
            'supported_crypto_currency' => 'nullable',
        ];
        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return response()->json($this->withError(collect($validate->errors())->collapse()));
        }

        $order_id = $request->order_id;
        $order = Order::where('id', $order_id)->first();

        if (!$order) {
            return response()->json($this->withError('Order not found'), 404);
        }

        $amount = $order->total;
        $gateway = $request->gateway_id;
        $currency = $request->supported_currency;
        $cryptoCurrency = $request->supported_crypto_currency;
        $user = Auth::user();

        $checkDeposit = Deposit::where('depositable_id', $order->id)
            ->where('depositable_type', 'App\\Models\\Order')
            ->first();

        if ($checkDeposit){
            return response()->json($this->withError('You have already sent the request for this order'));
        }

        if ($order->payment_status == 1) {
            return response()->json($this->withError('You already paid this order'));
        }

        try {
            if ($request->gateway_id != 2000 && $request->gateway_id != 2100) {

                $checkAmountValidate = $this->validationCheck($amount, $gateway, $currency, $cryptoCurrency , true);

                if ($checkAmountValidate['status'] == 'error') {
                    return response()->json($this->withError($checkAmountValidate['msg']));
                }
            }

            if (isset($checkAmountValidate['data']['gateway_id'])) {
                $gateway = $checkAmountValidate['data']['gateway_id'];
            } elseif ($request->gateway_id == 2100) {
                if ($user->balance < $amount) {
                    return response()->json($this->withError('Insufficient balance'));
                }
                $gateway = 2100;
            } else {
                $gateway = 2000;
            }

            if ($request->gateway_id == 2000 || $request->gateway_id == 2100) {
                if ($request->gateway_id == 2000) {
                    $walletPayment = $this->payment($order->id,  $amount, 'cashOnDelivery');
                } else {
                    $walletPayment = $this->payment($order->id,   $amount, 'walletPayment');
                }

                if ($walletPayment && $request->gateway_id == 2100 ) {
                    return response()->json($this->withSuccess('Payment successful'));
                }elseif ($walletPayment && $request->gateway_id == 2000){
                    return response()->json($this->withSuccess('Payment has pending'));
                } else {
                    throw new Exception('some thing is wrong');
                }
            }

            $deposit = Deposit::create([
                'user_id' => Auth::user() ? Auth::user()->id : 0,
                'payment_method_id' => $gateway,
                'payment_method_currency' => $checkAmountValidate['data']['currency'] ?? basicControl()->base_currency,
                'amount' => $checkAmountValidate['data']['amount'] ?? $order->total - $order->discount,
                'percentage_charge' => $checkAmountValidate['data']['percentage_charge'] ?? 0,
                'fixed_charge' => $checkAmountValidate['data']['fixed_charge'] ?? 0,
                'payable_amount' => $checkAmountValidate['data']['payable_amount'] ?? $order->total - $order->discount,
                'base_currency_charge' => $checkAmountValidate['data']['base_currency_charge'] ?? 0,
                'payable_amount_in_base_currency' => $checkAmountValidate['data']['payable_amount_base_in_currency'] ?? $order->total - $order->discount,
                'status' => 0,
            ]);

            $order->depositable()->save($deposit);
            return response()->json($this->withSuccess($deposit->trx_id));

        }catch (\Exception $e){
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function payment($order_id, $amount, $method)
    {
        $order = Order::where('id',$order_id)->first();
        try {

            if ($method == 'walletPayment') {
                $user = Auth::user();
                $user->balance = $user->balance - $amount;
                $user->update();
                $transaction = new Transaction();
                $transaction->user_id = Auth::user()->id;
                $transaction->amount = $amount;
                $transaction->charge = 0;
                $transaction->trx_type = '+';
                $transaction->remarks =  'Payment Via Wallet';
                $transaction->balance = $user->balance;
                $order->transactional()->save($transaction);
                $order->gateway_id = 2100;
                $order->payment_status = 1;
                $order->transaction_id =  $transaction->trx_id;
                $order->save();
            } else {
                $deposit = Deposit::create([
                    'user_id' => Auth::user() ? Auth::user()->id : 0,
                    'payment_method_id' => 2000,
                    'payment_method_currency' => basicControl()->base_currency ,
                    'amount' => $order->total,
                    'percentage_charge' => 0,
                    'fixed_charge' => 0,
                    'payable_amount' => $order->total ,
                    'base_currency_charge' => 0,
                    'payable_amount_in_base_currency' =>$order->total,
                    'status' => 2,
                ]);
                $order->depositable()->save($deposit);
                $order->gateway_id = 2000;
                $order->payment_status = 0;
                $order->transaction_id =  $deposit->trx_id;
                $order->save();
            }

            $params = [
                'order_number' => $order->order_number,
                'trx_id' => $order->transaction_id,
                'username' => $user->username??'Guest User',
                'payment_amount' => currencyPosition($order->total),
                'payment_method' => $method == 'walletPayment' ? 'Wallet' : 'Cash On Delivery',
                'datetime' => dateTime($order->created_at),
            ];

            $action = [
                "link" => "#",
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = '#';
            if (Auth::user()) {
                $this->sendMailSms(Auth::user(), 'PAYMENT_CONFIRMATION', $params);
                $this->userPushNotification(Auth::user(), 'PAYMENT_CONFIRMATION', $params, $action);
                $this->userFirebasePushNotification(Auth::user(), 'PAYMENT_CONFIRMATION', $params, $firebaseAction);
            }


            $actionAdmin = [
                "name" => Auth::user() ? Auth::user()->firstname . ' ' . Auth::user()->lastname : 'Guest User',
                "image" => Auth::user() ? getFile(Auth::user()->image_driver, Auth::user()->image) : 'Guest User',
                "link" => route('admin.transaction'),
                "icon" => "fas fa-ticket-alt text-white"
            ];

            $firebaseAction = "#";
            $this->adminMail('PAYMENT_CONFIRMATION_ADMIN', $params, $action);
            $this->adminPushNotification('PAYMENT_CONFIRMATION_ADMIN', $params, $actionAdmin);
            $this->adminFirebasePushNotification('PAYMENT_CONFIRMATION_ADMIN', $params, $firebaseAction);
            return true;
        }catch (\Exception $e){
            return false;
        }
    }
}

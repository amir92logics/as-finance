<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class OrderController extends Controller
{
    use Notify;
    public function index()
    {
        return view('admin.order.index');
    }

    public function orderListSearch(Request $request)
    {

        $orders = Order::query()->with(['user','gateway'])
            ->orderBy('created_at', 'desc')
            ->when(!empty($request->search['value']), function ($query) use ($request) {
                if (is_numeric($request->search['value']) || strlen($request->search['value']) === 1) {
                    return $query->whereDate('created_at', (int)$request->search['value']);
                } else {

                    try {
                        $carbonDate = Carbon::parse($request->search['value']);
                        return $query->whereDate('created_at', $carbonDate);
                    } catch (\Exception $e) {
                        $searchValue = $request->search['value'];

                        return $query->where(function ($query) use ($searchValue) {
                            $query->where('order_number', $searchValue)
                                ->orWhere(function ($query) use ($searchValue) {
                                    $query->where('first_name', 'LIKE', $searchValue)
                                        ->orWhere('last_name', 'LIKE', $searchValue)
                                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$searchValue]);
                                })
                                ->orWhereHas('user', function ($query) use ($searchValue) {
                                    $query->where('firstname', 'LIKE', $searchValue)
                                        ->orWhere('lastname', 'LIKE', $searchValue)
                                        ->orWhere('username', 'LIKE', $searchValue);
                                });
                        });
                    }
                }
            })
            ->whereNotNull('gateway_id')
            ->orderBy('created_at', 'desc');

        return DataTables::of($orders)
            ->addColumn('order_number',function ($item){
                return '<span class="">'.$item->order_number.'<span/>';


            })
            ->addColumn('order_date',function ($item){
                return dateTime($item->created_at);
            })
            ->addColumn('name',function ($item){
                if( $item->user){
                    $url = route('admin.user.view.profile', $item->user->id);
                    return '<a class="d-flex align-items-center me-2" href="' . $url . '">

                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-primary mb-0">' . $item->user->firstname . ' ' . $item->user->lastname . '</h5>
                                </div>
                              </a>';

                }elseif ($item->first_name){
                    return '<a class="d-flex align-items-center me-2" href="javascript:void(0)">

                                <div class="flex-grow-1 ms-3">
                                  <h5 class=" mb-0 text-danger">' . $item->first_name . ' ' . $item->last_name . '</h5>
                                </div>
                              </a>';
                }
                return '<a class="d-flex align-items-center me-2" href="javascript:void(0)">

                                <div class="flex-grow-1 ms-3">
                                  <h5 class="mb-0">' . $item->type??"Unknown User" . '</h5>
                                </div>
                              </a>';

            })
            ->addColumn('total',function ($item){
                return currencyPosition($item->total);
            })

            ->addColumn('status',function ($item){
                $id = $item->id;
                $bg = '';
                if ($item->payment_status == 0){
                    $bg = 'warning';
                    $status = 'Pending';
                }

                elseif ($item->payment_status == 1){
                    $bg = 'success';
                    $status = 'Paid';
                }
                elseif ($item->payment_status == 2){
                    $bg = 'danger';
                    $status = 'Cancelled';
                }

                return "<span id='paymentStatus_$id'><span class='badge  bg-soft-success text-$bg bg-soft-$bg text-$bg'><span class='legend-indicator bg-$bg'></span>".$status."</span></span>";
            })
            ->addColumn('order_status',function ($item){
                $html = '';
                $id = $item->id;
                if ($item->order_status == 0) {
                    $html .= '<option selected value="Pending">' . __("Pending") . '</option>';
                    $html .= '<option value="1">' . __("Accept") . '</option>';
                }elseif ($item->order_status == 1){
                    $html .= '<option ' . ($item->order_status == 1 ? 'selected' : '') . ' value="1">' . __("Order Placed") . '</option>';
                    $html .= '<option ' . ($item->order_status == 2 ? 'selected' : '') . ' value="2">' . __("Delivered") . '</option>';
                    $html .= '<option ' . ($item->order_status == 3 ? 'selected' : '') . ' value="3">' . __("Cancelled") . '</option>';
                }elseif ($item->order_status == 2){
                    $html .= '<option ' . ($item->order_status == 2 ? 'selected' : '') . ' value="2">' . __("Delivered") . '</option>';
                    $html .= '<option ' . ($item->order_status == 3 ? 'selected' : '') . ' value="3">' . __("Cancelled") . '</option>';
                }else{
                    $html .= '<option ' . ($item->order_status == 3 ? 'selected' : '') . ' value="3">' . __("Cancelled") . '</option>';
                }
               return "<select name='order_status_$id' id='order_status_$id' class='form-select' onchange='handelOrderStatus(event, \"$item->id \")' ><option disabled selected>Select type</option>
                            $html
                        </select>
                        ";
            })
            ->addColumn('method',function ($item){

                if ($item->gateway_id == 2000){
                    return '<a class="d-flex align-items-center me-2" href="javascript:void(0)">
                    <div class="flex-shrink-0">
                    <img class="avatar avatar-sm avatar-circle" src="'.asset('assets/admin/img/cash-payment.png').'" alt="Image Description">
                    </div>
                    <div class="flex-grow-1 ms-3">
                      <h5 class="text-hover-primary mb-0">'.trans('Cash on Delivery').'</h5>
                    </div>
                  </a>';

                }elseif ($item->gateway_id == 2100){
                    return '<a class="d-flex align-items-center me-2" href="javascript:void(0)">
                    <div class="flex-shrink-0">
                    <img class="avatar avatar-sm avatar-circle" src="'.asset('assets/admin/img/wallet_1.png').'" alt="Image Description">
                    </div>
                    <div class="flex-grow-1 ms-3">
                      <h5 class="text-hover-primary mb-0">'.trans('Wallet').'</h5>
                    </div>
                  </a>';
                }
                return '<a class="d-flex align-items-center me-2" href="javascript:void(0)">
                                <div class="flex-shrink-0">
                                 <div class="avatar avatar-sm avatar-circle">
                                        <img class="avatar-img" src="' . getFile($item->gateway->driver,$item->gateway->image) . '" alt="Image Description">
                                     </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . optional($item->gateway)->name . '</h5>
                                </div>
                              </a>';


            })
            ->addColumn('action',function ($item){
                $viewUrl = route('admin.order.show', $item->id);
                $editUrl = route('admin.order.edit', $item->id);
                return "<div class='btn-group' role='group'>
                    <a class='btn btn-white btn-sm' href='$viewUrl'>
                      <i class='bi-eye'></i> View
                    </a>

                    <!-- Button Group -->
                    <div class='btn-group'>
                      <button type='button' class='btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty' id='ordersExportDropdown1' data-bs-toggle='dropdown' aria-expanded='false'></button>

                      <div class='dropdown-menu dropdown-menu-end mt-1' aria-labelledby='ordersExportDropdown1' style=''>

                        <a class='dropdown-item'  href='$editUrl'>
                           <i class='bi-pencil-fill dropdown-item-icon'></i> Edit
                         </a>
                        <a class='dropdown-item' onclick='getPrintInvoice(\"$item->id\")' href='javascript:void(0)'>
                         <i class='fa-solid fa-download dropdown-item-icon'></i> Invoice
                        </a>
                      </div>
                    </div>
                    <!-- End Unfold -->
                  </div>";


            })
            ->rawColumns(['order_number','total','order_date','name','status','order_status','method','action'])
            ->make(true);


    }

    public function show($orderId)
    {

        $userOrders = [];
        $data['order'] = Order::with(['gateway', 'orderItem',  'orderItem.product.details',  'user', 'area'])->findOrFail($orderId);
        if ($data['order']->user){
            $userOrders = Order::where('user_id',$data['order']->user_id)->where('gateway_id', '!=', 'null')->get();

        }
        return view('admin.order.show', $data,compact('userOrders'));
    }

    public function edit($orderId)
    {
        $data['order'] = Order::with(['gateway', 'orderItem',  'orderItem.product.details',  'user', 'area'])->findOrFail($orderId);
        $data['products'] = Product::with('details')
            ->where('is_published', 1)
            ->get();
        $data['categories'] = Category::select('id', 'name','status')->get();
        return view('admin.order.edit', $data,compact('data'));
    }

    public function getOrderCalculation(Request $request)
    {
        $order_id = $request->order_id;
        $order = Order::where('id', $order_id)->first();
        if (!$order){
            return response()->json(['error' => 'Order not found']);
        }
        $area_id = $order->area_id;
        $area = Area::where('id', $area_id)->first();
        if (!$area){
            return response()->json(['error' => 'Area not found']);
        }
        $totalOrder = $request->quantity;
        $price = $request->price;
        $totalDeliveryCharge = 0;

        foreach ($area->shippingCharge as $item) {
            $orderFrom = intval($item['order_from']);
            $orderTo = $item['order_to'];
            if ($orderTo){
                if ($totalOrder >= $orderFrom && $totalOrder <= $orderTo){
                    $totalDeliveryCharge = $item['delivery_charge'];
                }
            }else{
                if ($totalOrder >= $orderFrom){
                    $totalDeliveryCharge = $item['delivery_charge'];
                }

            }
        }
        $vat = ($price*basicControl()->vat)/100;
        if ($order->coupon_code){
            $coupon = Coupon::where('coupon_code', $order->coupon_code)->first();
            if (!$coupon){
                return response()->json(['error' => 'Coupon not found']);
            }
            $discount = adminDiscountPrice($price,$coupon->coupon_code,$price);
            return response()->json(['discount' => $discount,'delivery_charge' => $totalDeliveryCharge,'vat' => $vat]);

        }

        return response(['delivery_charge' => $totalDeliveryCharge,'vat' => $vat]);


    }
    public function updateOrder(Request $request,$id)
    {

        $request->validate([
            'products.*' => 'required',
        ]);

        $order = Order::findOrFail($id);

        $products = $request->products;
        $orderItmes = [];
        $subTotal = 0;
        $totalOrder = 0;
        foreach ($products as $key => $item){
            $product = Product::select('id' ,'price')->findOrFail($key);
            $subTotal += $product->price * $item['quantity'];
            $totalOrder += $item['quantity'];
            $orderItmes[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price
            ];
        }
        DB::beginTransaction();
        try {
            $vat = vat($subTotal);
            $area = $order->area;
            foreach ($area->shippingCharge as $item) {
                $orderFrom = intval($item['order_from']);
                $orderTo = $item['order_to'];
                if ($orderTo){
                    if ($totalOrder >= $orderFrom && $totalOrder <= $orderTo){
                        $totalDeliveryCharge = $item['delivery_charge'];
                    }
                }else{
                    if ($totalOrder >= $orderFrom){
                        $totalDeliveryCharge = $item['delivery_charge'];
                    }

                }
            }

            $discount = 0;
            if ($order->coupon_code){
                $coupon = Coupon::where('coupon_code', $order->coupon_code)->first();
                if (!$coupon){
                    return response()->json(['error' => 'Coupon not found']);
                }
                $price = $subTotal;
                if ($coupon->applicableProducts->first()){
                    $price = 0;
                    $productIds = array_column($orderItmes, 'product_id');
                    $applicableProducts = $coupon->applicableProducts->pluck('product_id')->toArray();
                    $matchedItems = array_intersect($applicableProducts,$productIds);
                    foreach ($orderItmes as $item){
                        if (in_array($item['product_id'], $matchedItems)){
                            $price += $item['price'] * $item['quantity'];
                        }
                    }
                }
                $discountPrice = adminDiscountPrice($price,$coupon->coupon_code,$subTotal);
                $discount = $discountPrice['discount'];

            }

            $total = ($subTotal+$totalDeliveryCharge + $vat) - $discount;

            $due = $total - $order->total;


            if ($order->due){
                $order->due = $order->due + $due   ;
            }else{
                $order->due =   $due;
            }
            $order->total = $total;
            $order->subtotal = $subTotal;
            $order->discount = $discount;
            $order->vat = $vat;
            $order->delivery_charge = $totalDeliveryCharge;
            $order->save();

            $order->orderItem()->delete();
            $order->orderItem()->createMany($orderItmes);
            DB::commit();
            return redirect()->route('admin.order.show', $order->id)->with('success', 'Order updated successfully');
        }catch (\Exception $e){
            DB::rollback();
            return back()->with('error',$e->getMessage());
        }
    }

    public function updateOrderStatus(Request $request)
    {
        $purifiedData = $request->all();

        $rules = [
            'status' => ['required', Rule::in(1,2,3)],
        ];


        $validator = Validator::make($purifiedData, $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['errors' => $errors], 422);
        }
        if ($request->orderId == null) {
            session()->flash('error', 'You do not select Any Order Item.');
            return response()->json(['error' => 1]);
        }else {

            $order = Order::where('id',$request->orderId)->first();
            if (!$order){
                session()->flash('error', 'Order not found.');
                return response()->json(['error' => 1]);
            }
            if ($order->order_status == 3){
                session()->flash('error', 'Order is already Canceled.');
                return response()->json(['error' => 1]);
            }
            DB::beginTransaction();
            try {
                $order->order_status = $request->status;
                $order->save();


                $msg = [
                    'username' =>optional($order->user)->username,
                    'order_number' => $order->order_number,
                ];

                $action2 = [
                    "link" => '#',
                    "icon" => "fa-regular fa-bell"
                ];
                if ($request->status == 1){
                    $status = 'Accept';
                    $action = [
                        "link" => route('admin.order.index'),
                        "icon" => "fa-regular fa-bell"
                    ];

                    if ($order->user){
                        $action = [
                            "name" => optional($order->user)->firstname . ' ' . optional($order->user)->lastname,
                            "image" => getFile(optional($order->user)->image_driver, optional($order->user)->image),
                            "link" => route('admin.order.index'),
                            "icon" => "fa-regular fa-bell"
                        ];

                        $msg2 = [
                            'username' =>optional($order->user)->username,
                            'ordernumber'=> $order->order_number,
                            'orderdate' => dateTime($order->created_at),
                            'total' => $order->total
                        ];
                        $this->sendMailSms($order->user, 'ORDER_ACCEPTED_USER', $msg2);
                        $this->userPushNotification($order->user,'ORDER_ACCEPTED_USER',$msg2,$action2,);
                        $this->userFirebasePushNotification($order->user, 'ORDER_ACCEPTED_USER', $msg2, ["link" => '#']);
                    }
                    $this->adminPushNotification('ORDER_ACCEPTED', $msg, $action);
                    $this->adminFirebasePushNotification('ORDER_ACCEPTED', $msg, $action);
                    $this->adminMail("ORDER_ACCEPTED", $msg);
                }elseif ($request->status == 2){
                    $status = 'Delivered';
                    if ($order->user){
                        $this->sendMailSms($order->user, 'ORDER_DELIVERD', $msg);
                        $this->userPushNotification($order->user,'ORDER_DELIVERD',$msg,$action2,);
                        $this->userFirebasePushNotification($order->user, 'ORDER_DELIVERD', $msg, ["link" => '#']);
                    }

                }else{
                    $status = 'Cancelled';
                    $order->payment_status = 2;
                    $order->save();
                    if ($order->user){
                        $user = $order->user;
                        $user->balance = $user->balance + $order->total;
                        $user->save();
                        $this->sendMailSms($order->user, 'ORDER_CANCELED', $msg);
                        $this->userPushNotification($order->user,'ORDER_CANCELED',$msg,$action2,);
                        $this->userFirebasePushNotification($order->user, 'ORDER_CANCELED', $msg, ["link" => '#']);
                    }

                }
                session()->flash('success', 'Order Status Has Been ' . $status);
                DB::commit();
                return response()->json(['success' => 1, 'status' => $status]);
            }catch (\Exception $e){
                DB::rollback();
                return response()->json(['error' => $e->getMessage()]);
            }
        }
    }

    public function showPrintInvoice($id = null)
    {

        $data['title'] = "Print Invoice";
        $data['order'] = Order::with('orderItem.product.details')->findOrFail($id);
        return view('admin.order.print-Invoice', $data);
    }
}

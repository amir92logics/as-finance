<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponApplicableProducts;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CouponController extends Controller
{
    public function index()
    {
        return view('admin.coupon.index');
    }

    public function couponSearch(Request $request)
    {
        $search = @$request->search['value'];
        $coupons = Coupon::query()
            ->when(!empty($search), function ($query) use ($search) {
                $query->where('coupon_code', 'LIKE', "%{$search}%");
            })
            ->orderBy('id', 'DESC');

        return DataTables::of($coupons)
            ->addColumn('no', function ($item) {
                static $counter = 0;
                $counter++;
                return $counter;
            })
            ->addColumn('coupon-code', function ($item) {
                return $item->coupon_code;
            })
            ->addColumn('start-date', function ($item) {
                return dateTime($item->start_date);
            })
            ->addColumn('end-date', function ($item) {
                $endDate = dateTime($item->end_date);
                $currentDate = now();

                if ($item->end_date < $currentDate) {
                    return '<span class="text-danger">' . $endDate . '</span>';
                } else {
                    return '<span class="text-success">' . $endDate . '</span>';
                }
            })
            ->addColumn('action', function ($item) {
                $editUrl = route('admin.product.coupon.edit', $item->id);
                $deleteUrl = route('admin.product.coupon.destroy', $item->id);
                return '<div class="btn-group" role="group">
                            <a class="btn btn-white btn-sm" href="' . $editUrl . '">
                                <i class="bi-pencil-fill me-1"></i>' . trans("Edit") . '
                            </a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="productsEditDropdown1" data-bs-toggle="dropdown" aria-expanded="false"></button>

                                <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="productsEditDropdown1">
                                    <a class="dropdown-item deleteBtn text-danger"
                                       href="javascript:void(0)"
                                       data-route="' . $deleteUrl . '"
                                       data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="bi-trash dropdown-item-icon text-danger"></i> ' . trans("Delete") . '
                                    </a>
                                </div>
                            </div>
                        </div>';
            })
            ->rawColumns(['no', 'coupon-code', 'start-date', 'end-date', 'action'])
            ->make(true);
    }

    public function create()
    {
        $products = Product::with('details')
            ->where('is_published', 1)->get();
        return view('admin.coupon.create',compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|regex:/^[a-zA-Z0-9\s-]+$/|unique:coupons,coupon_code',
            'expiration_validity' => 'required',
            'discount' => 'required|not_in:0',
            'discount_type' => 'required',
            'maximum_order' => 'required|min:1',
            'applicable_products' => 'nullable|array',
            'applicable_products.*' => 'exists:products,id',
        ]);

        list($startDate, $endDate) = array_map('trim', explode('-', $request->expiration_validity));

        try {
            $coupon = new Coupon();
            $coupon->coupon_code = $request->coupon_code;
            $coupon->minimum_order_price = $request->minimum_order_price;
            $coupon->start_date = Carbon::createFromFormat('d/m/Y', $startDate)->toDateString();
            $coupon->end_date = Carbon::createFromFormat('d/m/Y', $endDate)->toDateString();
            $coupon->discount = $request->discount;
            $coupon->discount_type = $request->discount_type;
            $coupon->maximum_order = $request->maximum_order;
            $coupon->save();

            if ($request->applicable_products){
                foreach ($request->applicable_products as $applicable_product){
                    $applicableProduct = new CouponApplicableProducts();
                    $applicableProduct->coupon_id =  $coupon->id;
                    $applicableProduct->product_id  = $applicable_product;
                    $applicableProduct->save();
                }
            }
            return redirect()->route('admin.product.coupon')->with('success', 'Coupon Created Successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function edit($id)
    {
        try {
            $coupon = Coupon::where('id', $id)->firstOr(function () {
                throw new \Exception('No Coupon data found.');
            });
            $data['coupon'] = $coupon;
            $data['products'] = Product::with('details')
                ->where('is_published', 1)->get();
            $data['applicableProducts'] = $coupon->applicableProducts->pluck('product_id')->toArray();
            $startDate = Carbon::parse($coupon->start_date)->format('d/m/Y');
            $endDate = Carbon::parse($coupon->end_date)->format('d/m/Y');
            $data['expirationValidity'] =  $startDate .' - '. $endDate;
            return view('admin.coupon.edit', $data);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'coupon_code' => array('required', 'regex:/^[a-zA-Z0-9\s-]+$/'),
            'expiration_validity' => 'required',
            'discount' => 'required|not_in:0',
            'discount_type' => 'required',
            'maximum_order' => 'required|min:1',
            'applicable_products' => 'nullable|array',
            'applicable_products.*' => 'exists:products,id',
        ]);
        list($startDate, $endDate) = array_map('trim', explode('-', $request->expiration_validity));

        try {
            $coupon = Coupon::where('id', $id)->firstOr(function () {
                throw new \Exception('No Coupon data found.');
            });

            $coupon->coupon_code = $request->coupon_code;
            $coupon->minimum_order_price = $request->minimum_order_price;
            $coupon->start_date = Carbon::createFromFormat('d/m/Y', $startDate)->toDateString();
            $coupon->end_date = Carbon::createFromFormat('d/m/Y', $endDate)->toDateString();
            $coupon->discount = $request->discount;
            $coupon->discount_type = $request->discount_type;
            $coupon->maximum_order = $request->maximum_order;
            $coupon->save();

            if ($request->applicable_products) {
                $newProductIds = collect($request->applicable_products)->unique();

                // Delete any existing products for this coupon that are not in the new list
                $coupon->applicableProducts()
                    ->whereNotIn('product_id', $newProductIds)
                    ->delete();

                // Insert or update each item in the new applicable products list
                foreach ($newProductIds as $productId) {
                    CouponApplicableProducts::updateOrCreate(
                        [
                            'coupon_id' => $coupon->id,
                            'product_id' => $productId,
                        ],
                        [
                            'coupon_id' => $coupon->id,
                            'product_id' => $productId,
                        ]
                    );
                }
            }


            return redirect()->route('admin.product.coupon')->with('success', 'Coupon Updated Successfully');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            $coupon = Coupon::where('id', $id)->firstOr(function () {
                throw new \Exception('No Coupon data found.');
            });
            $coupon->delete();
            return redirect()->back()->with('success', 'Product Coupon Deleted Successfully.');
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }


}

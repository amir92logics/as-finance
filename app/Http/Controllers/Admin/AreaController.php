<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;
use Yajra\DataTables\DataTables;

class AreaController extends Controller
{
    //get all areas
    public function index()
    {
        return view('admin.area.index');
    }

    public function getAreaList(Request $request)
    {
        $areas = Area::query()
            ->withCount("shippingCharge")
            ->when(!empty($request->search['value']),function ($query)use ($request){
                $query->where('area_name','LIKE','%'.$request->search['value'].'%')
                    ->orWhere('post_code',$request->search['value']);
            });
        return DataTables::of($areas)
            ->addColumn('no',function (){
                static $count = 0;
                return ++$count;
            })
            ->addColumn('area_name',function ($item){
                return $item->area_name;
            })
            ->addColumn('post_code',function ($item){
                return $item->post_code;
            })
            ->addColumn('shipping_charge',function ($item){
                return optional($item)->shipping_charge_count;
            })
            ->addColumn('action',function ($item){
                $editUrl = route('admin.area.edit',$item->id);
                $deleteUrl = route('admin.area.delete', $item->id);
                $actionHtml = '';
                $actionHtml = "<div class='btn-group' role='group'>
                      <a href='$editUrl' class='btn btn-white btn-sm edit_user_btn'>
                        <i class='bi bi-pencil-square dropdown-item-icon'></i> ".trans('Edit')."
                      </a>
                    <div class='btn-group'>
                      <button type='button' class='btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty' id='userEditDropdown' data-bs-toggle='dropdown' aria-expanded='false'></button>
                      <div class='dropdown-menu dropdown-menu-end mt-1' aria-labelledby='userEditDropdown'>
                          <a class='dropdown-item loginAccount deleteButton' href='javascript:void(0)' data-route='$deleteUrl' data-bs-toggle='modal' data-bs-target='#deleteModal'>
                           <i class='bi bi-trash dropdown-item-icon'></i>
                          ".trans("Delete")."
                        </a>
                      </div>
                    </div>
                  </div>";
                return $actionHtml;
            })
            ->rawColumns(['no','area_name','post_code','shipping_charge','action'])
            ->make(true);
    }

    //    create area from

    public function create()
    {
        return view('admin.area.create');
    }

    //store new areas
    public function store(Request $request)
    {
        //   validate request
        $purifiedData = Purify::clean($request->except('_token', '_method'));

        $rules = [
            'area_name' => ['required', 'string'],
            'post_code' => ['required', 'regex:/^[0-9]{3,7}$/'],
            'shipping_price_range' => ['required', 'array', 'min:1'],
            'shipping_price_range.*.order_from' => ['required', 'numeric'],
            'shipping_price_range.*.order_to' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    $shippingPriceRange = $request->shipping_price_range;
                    $lastItemIndex = count($shippingPriceRange) - 1;
                    if ($shippingPriceRange[$lastItemIndex]["order_to"] != "") {
                        $fail('The order to must be add upto all orders ');
                    }
                },
            ],
            'shipping_price_range.*.delivery_charge' => ['required', 'numeric'],
        ];

        $messages = [
            'area_name.required' => 'The area name is required.',
            'area_name.string' => 'The area name must be a string.',
            'post_code.required' => 'The post code is required.',
            'post_code.regex' => 'The post code must be a numeric value between 3 and 7 digits.',
            'shipping_price_range.required' => 'At least one shipping price range is required.',
            'shipping_price_range.array' => 'The shipping price range must be an array.',
            'shipping_price_range.min' => 'At least one shipping price range is required.',
            'shipping_price_range.*.order_from.required' => 'The order from value is required for each shipping price range.',
            'shipping_price_range.*.order_from.numeric' => 'The order from value must be a numeric value.',
            'shipping_price_range.*.order_to.numeric' => 'The order to value must be a numeric value.',
            'shipping_price_range.*.delivery_charge.required' => 'The delivery charge value is required for each shipping price range.',
            'shipping_price_range.*.delivery_charge.numeric' => 'The delivery charge value must be a numeric value.',
        ];
        $validate = Validator::make($purifiedData, $rules, $messages);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }
        //    store database
        $area = new Area();
        $area->area_name = $purifiedData['area_name'];
        $area->post_code = $purifiedData['post_code'];
        $area->save();
        $area->shippingCharge()->createMany($purifiedData['shipping_price_range']);
        return redirect()->route('admin.area.index')->with('success', 'Area created successfully');
    }

    //    get specifiq area
    public function edit($id)
    {
        $data['area'] = Area::with('shippingCharge')->findOrFail($id);
        return view('admin.area.edit', $data);
    }

    //    update area
    public function update(Request $request, $id)
    {

        //   validate request
        $purifiedData = Purify::clean($request->except('_token', '_method'));

        $rules = [
            'area_name' => ['required', 'string'],
            'post_code' => ['required', 'regex:/^[0-9]{3,7}$/'],
            'shipping_price_range' => ['required', 'array', 'min:1'],
            'shipping_price_range.*.order_from' => ['required', 'numeric'],
            'shipping_price_range.*.order_to' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    $shippingPriceRange = $request->shipping_price_range;
                    $lastItemIndex = count($shippingPriceRange) - 1;
                    if ($shippingPriceRange[$lastItemIndex]["order_to"] != "") {
                        $fail('The order to must be add upto all orders ');
                    }
                },
            ],
            'shipping_price_range.*.delivery_charge' => ['required', 'numeric'],
        ];
        $messages = [
            'area_name.required' => 'The area name is required.',
            'area_name.string' => 'The area name must be a string.',
            'post_code.required' => 'The post code is required.',
            'post_code.regex' => 'The post code must be a numeric value between 3 and 7 digits.',
            'shipping_price_range.required' => 'At least one shipping price range is required.',
            'shipping_price_range.array' => 'The shipping price range must be an array.',
            'shipping_price_range.min' => 'At least one shipping price range is required.',
            'shipping_price_range.*.order_from.required' => 'The order from value is required for each shipping price range.',
            'shipping_price_range.*.order_from.numeric' => 'The order from value must be a numeric value.',
            'shipping_price_range.*.order_to.numeric' => 'The order to value must be a numeric value.',
            'shipping_price_range.*.delivery_charge.required' => 'The delivery charge value is required for each shipping price range.',
            'shipping_price_range.*.delivery_charge.numeric' => 'The delivery charge value must be a numeric value.',
        ];
        $validate = Validator::make($purifiedData, $rules, $messages);

        if ($validate->fails()) {

            return back()->withInput()->withErrors($validate);
        }
        //    update database
        $area = Area::findOrFail($id);
        $area->area_name = $purifiedData['area_name'];
        $area->post_code = $purifiedData['post_code'];
        $area->save();
        $area->shippingCharge()->delete();
        $area->shippingCharge()->createMany($purifiedData['shipping_price_range']);

        return redirect()->route('admin.area.index')->with('success', 'Area updated successfully');
    }


    //    delete area
    public function delete($id)
    {
        Area::destroy($id);
        return redirect()->back()->with('success', 'Area updated successfully');
    }
}

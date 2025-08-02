<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductDetails;
use App\Models\ProductMedia;
use App\Models\ProductPrice;
use App\Models\Project;
use App\Models\SubCategory;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use function Laravel\Prompts\select;

class ProductController extends Controller
{
    use Upload;
    public function index()
    {
        $languages = Language::where('status',1)->get();
        $totalProducts = Product::where('status', 'Available')->count();
        return view('admin.product.index', compact('totalProducts','languages'));
    }

    public function productList(Request $request)
    {
        $search = @$request->search['value'];
        $filterProductName = $request->filterProductName;
        $filterStatus = $request->filterStatus;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;
        $languages = Language::where('status',1)->get();
        $defaultLanguage = Language::where('default_status',1)->first();
        $allProductsId = Product::query()->select('id')->get()->pluck('id')->toArray();

        $productDetails = DB::table('product_details')
            ->whereIn('product_id',  $allProductsId)
            ->get()
            ->groupBy('product_id')
            ->map(function ($details) {
                return $details->pluck('language_id')->flip()->all();
            })
            ->toArray();

        $products = Product::query()->with(['category','subcategory','details','reviews'])
            ->whereHas('details', function ($query) use ($defaultLanguage) {
                $query->where('language_id', $defaultLanguage->id);
            })
            ->orderBy('id', 'DESC')
            ->when(!empty($search), function ($query) use ($search) {
                return $query->whereHas('details', function ($query) use ($search) {
                    return $query->where('title', 'LIKE', "%{$search}%");
                });
            })
            ->when(!empty($filterProductName), function ($query) use ($filterProductName) {
                return $query->whereHas('details', function ($query) use ($filterProductName) {
                    return $query->where('title', 'LIKE', "%{$filterProductName}%");
                });
            })
            ->when($filterStatus !== null && $filterStatus !== "all", function ($query) use ($filterStatus) {
                return $query->where('status', $filterStatus);
            })
            ->when($startDate && !$endDate, function ($query) use ($startDate) {
                $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $startDate);
                return $query->whereDate('created_at', $startDate);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $startDate);
                $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', $endDate);
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            });

        return DataTables::of($products)
            ->addColumn('checkbox', function ($item) {
                return ' <input type="checkbox" id="chk-' . $item->id . '"
                                       class="form-check-input row-tic tic-check" name="check" value="' . $item->id . '"
                                       data-id="' . $item->id . '">';
            })
            ->addColumn('product', function ($item) {
                $img = getFile($item->driver,$item->thumbnail_image);
                return '<a class="d-flex align-items-center" href="javascript:void(0)">
                        <div class="flex-shrink-0">
                          <img class="avatar avatar-lg" src="'.$img.'" alt="Image">
                        </div>
                        <div class="flex-grow-1 ms-3">
                          <h5 class="text-inherit mb-0">'.Str::words($item->title, 6).'</h5>
                        </div>
                  </a>';
            })
            ->addColumn('category', function ($item) {
                return $item->category->name ?? 'no-category';
            })
            ->addColumn('subcategory', function ($item) {
                return $item->subcategory->name ?? 'no-subcategory';
            })
            ->addColumn('price', function ($item) {
                return currencyPosition($item->price);
            })
            ->addColumn('availability', function ($item) {
                $class = $item->status == 'Available' ? 'success' : 'warning';
                return "<span class='badge bg-soft-$class text-$class'><span class='legend-indicator bg-$class'></span> " . $item->status . "</span>";
            })
            ->addColumn('status', function ($item) {
                $class = $item->is_published == 1 ? 'success' : 'danger';
                return "<span class='badge bg-soft-$class text-$class'><span class='legend-indicator bg-$class'></span> " . ($item->is_published == 1?'Published':'Unpublished'). "</span>";
            })
            ->addColumn('language', function ($item)use ($languages,$productDetails) {
                $lang = '';
                foreach($languages as $language){
                    $lang .= ' <a href="'. route('admin.product.edit', [$item->id, $language->id]) .'"
                                          class="btn btn-white btn-icon btn-sm flag-btn"
                                          >
                                           <i class="bi '.(isset($productDetails[$item->id][$language->id]) ? 'bi-check2' : 'bi-pencil').'"></i>
                                       </a>';
                }

                return $lang;
            })
            ->addColumn('action', function ($item) {
                $editUrl = route('admin.product.edit', [$item->id,optional($item->details)->language_id]);
                $seoUrl = route('admin.product.seo', $item->id);
                $deleteUrl = route('admin.product.destroy', $item->id);
                return '<div class="btn-group" role="group">
                        <a class="btn btn-white btn-sm" id="updatedVariantsComObjValues" href="'.$editUrl.'"
                                       data-product-id="'.$item->id.'">
                            <i class="bi-pencil-fill me-1"></i>' . trans("Edit") . '
                        </a>
                        <div class="btn-group">
                            <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="productsEditDropdown1" data-bs-toggle="dropdown" aria-expanded="false"></button>

                            <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="productsEditDropdown1">
                                <a class="dropdown-item"
                                   href="'.$seoUrl.'">
                                    <i class="fa-light fa-magnifying-glass dropdown-item-icon"></i>' . trans("SEO") . '
                                </a>
                                <a class="dropdown-item deleteBtn text-danger"
                                   href="javascript:void(0)"
                                   data-route="' . $deleteUrl . '"
                                   data-item-name="' . $item->title . '"
                                   data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="bi-trash dropdown-item-icon text-danger"></i> ' . trans("Delete") . '
                                </a>
                            </div>
                        </div>
                    </div>';
            })
            ->rawColumns(['checkbox', 'product', 'category', 'subcategory', 'price','availability', 'status','language', 'action'])
            ->make(true);

    }

    public function create()
    {
        $data['language'] = Language::where('default_status',1)->firstOrFail();
        $data['categories'] = Category::select('id', 'name','status')->get();
        return view('admin.product.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:250',
            'price' => 'required|numeric|min:1',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:sub_categories,id',
            'quantity' => 'required|numeric|min:1',
            'quantity_type' => 'required|in:gm,kg,pcs,liter',
            'description' => 'required|string|max:5000',
            'short_description' => 'nullable|string|max:2000',
            'status' => 'required|in:Available,Stock Out',
            'is_published' => 'required|in:1,0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        DB::beginTransaction();

        try {
            $slug = $this->generateUniqueSlug($request->title);

            if ($request->hasFile('thumbnail')) {
                $thumbnail = $this->fileUpload($request->thumbnail,config('filelocation.product.path'),null,null,'webp',90);
            }
            $product = new Product();
            $product->price = $request->price;
            $product->category_id = $request->category_id;
            $product->subcategory_id = $request->subcategory_id;
            $product->quantity = $request->quantity;
            $product->quantity_unit = $request->quantity_type;
            $product->status = $request->status;
            $product->is_published = $request->is_published;
            $product->thumbnail_image = $thumbnail['path']??null;
            $product->driver = $thumbnail['driver']??null;
            $product->save();

            $details = new ProductDetails();
            $details->title = $request->title;
            $details->description = $request->description;
            $details->short_description = $request->short_description;
            $details->slug = $slug;
            $details->language_id = $request->language_id;
            $details->product_id = $product->id;
            $details->save();

            DB::commit();
            return redirect()->route('admin.products')->with('success', 'Product created successfully.');
        }catch (\Exception $exception){
            DB::rollBack();
            return back()->with('error', $exception->getMessage());
        }


    }


    public function edit($id,$language_id)
    {
        $product = Product::with(['details' => function ($query) use ($language_id) {
            $query->where('language_id',$language_id);
        }])->findOrFail($id);
        $categories = Category::select('id', 'name','status')->get();

        return view('admin.product.edit', compact('product', 'language_id','categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:250',
            'price' => 'required|numeric|min:1',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:sub_categories,id',
            'quantity' => 'required|numeric|min:1',
            'quantity_type' => 'required|in:gm,kg,pcs,liter',
            'description' => 'required|string|max:5000',
            'short_description' => 'nullable|string|max:2000',
            'status' => 'required|in:Available,Stock Out',
            'is_published' => 'required|in:1,0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        DB::beginTransaction();

        $language = $request->language_id;
        $product = Product::with(['details' => function ($query) use ($language) {
            $query->where('language_id',$language);
        }])->findOrFail($id);
        try {
            $slug = $this->generateUniqueSlug($request->title,optional($product->details)->id);

            if ($request->hasFile('thumbnail')) {
                $thumbnail = $this->fileUpload($request->thumbnail,config('filelocation.product.path'),null,null,'webp',90,$product->thumbnail_image,$product->driver);
            }
            $product->price = $request->price;
            $product->category_id = $request->category_id;
            $product->subcategory_id = $request->subcategory_id;
            $product->quantity = $request->quantity;
            $product->quantity_unit = $request->quantity_type;
            $product->status = $request->status;
            $product->is_published = $request->is_published;
            $product->thumbnail_image = $thumbnail['path']??$product->thumbnail_image;
            $product->driver = $thumbnail['driver']??$product->driver;
            $product->save();

            $product->details()->updateOrCreate(
                ['language_id' => $request->language_id], // Matching condition
                [
                    'title' => $request->title,
                    'description' => $request->description,
                    'short_description' => $request->short_description,
                    'slug' => $slug,
                    'product_id' => $product->id
                ]
            );


            DB::commit();
            return redirect()->route('admin.products')->with('success', 'Product created successfully.');
        }catch (\Exception $exception){
            DB::rollBack();
            return back()->with('error', $exception->getMessage());
        }
    }


    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $product = Product::with(['media', 'variants_price'])->where('id', $id)->firstOr(function () {
                throw new \Exception('No Product data found.');
            });

            $this->fileDelete($product->driver, $product->thumbnail_image);
            $product->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }



    public function getSubcategory(Request $request)
    {
        $subcategories = SubCategory::where('category_id', $request->category_id)->get();
        return response()->json([
            'status' => true,
            'data' => $subcategories
        ]);
    }

    private function generateUniqueSlug($title, $id = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        // Determine the correct model to check the slug against
        $query = ProductDetails::where('slug', $slug);

        if ($id !== null) {
            $query->where('id', '!=', $id);
        }

        // Check if the slug exists and append a number if it does
        while ($query->exists()) {
            $slug = $originalSlug . '-' . $count++;
            $query = ProductDetails::where('slug', $slug);

            if ($id !== null) {
                $query->where('id', '!=', $id);
            }
        }

        return $slug;
    }

    public function productSeo($id)
    {
        try {
            $product = Product::where('id', $id)->firstOr(function () {
                throw new \Exception('No Product data found.');
            });
            return view('admin.product.seo', compact('product'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function productSeoUpdate(Request $request, $id)
    {
        $request->validate([
            'page_title' => 'required|string|min:3|max:100',
            'meta_title' => 'required|string|min:3|max:100',
            'meta_keywords' => 'required|array',
            'meta_keywords.*' => 'required|string|min:1|max:300',
            'meta_description' => 'required|string|min:1|max:300',
            'meta_image' => 'sometimes|required|mimes:jpeg,png,jpeg|max:2048'
        ]);

        try {
            $product = Product::where('id', $id)->firstOr(function () {
                throw new \Exception('No Product data found.');
            });
            if ($request->hasFile('meta_image')) {
                try {
                    $image = $this->fileUpload($request->meta_image, config('filelocation.pageSeo.path'), null, null, 'webp', 99, $product->meta_image_Driver, $product->meta_image);
                    if ($image) {
                        $pageSEOImage = $image['path'];
                        $pageSEODriver = $image['driver'] ?? 'local';
                    }
                } catch (\Exception $exp) {
                    return back()->with('error', 'Meta image could not be uploaded.');
                }
            }

            $product->page_title = $request->page_title;
            $product->meta_title = $request->meta_title;
            $product->meta_keywords = $request->meta_keywords;
            $product->meta_description = $request->meta_description;
            $product->meta_image = $pageSEOImage ?? $product->meta_image;
            $product->meta_image_Driver = $pageSEODriver ?? $product->meta_image_driver;
            $product->save();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Seo has been updated.');
    }


    public function productDestroyMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any Product.');
            return response()->json(['error' => 1]);
        } else {
            DB::transaction(function () use ($request) {
                Product::with(['details'])->whereIn('id', $request->strIds)->get()->map(function ($product){
                    $this->fileDelete($product->driver, $product->thumbnail_image);
                    $product->details()->delete();
                    $product->delete();
                });
            });
            session()->flash('success', 'Product has been deleted successfully');
            return response()->json(['success' => 1]);
        }
    }

    public function filterProduct(Request $request)
    {
        $category_id = $request->category_id;
        $subCategoryId = $request->subcategory_id;
        $products = Product::with(['details','category','subcategory'])
            ->when($category_id, function ($query) use ($category_id) {
                return $query->where('category_id', $category_id);
            })
            ->when($subCategoryId, function ($query) use ($subCategoryId) {
                return $query->where('subcategory_id', $subCategoryId);
            })
           ->get();
        return response()->json(['products' => $products]);
    }


}

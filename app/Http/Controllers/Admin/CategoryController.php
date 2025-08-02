<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    use Upload;
    public function index()
    {
        return view('admin.category.index');
    }

    public function categoriesSearch(Request $request)
    {
        $search = @$request->search['value'];
        $categories = Category::query()
            ->when(!empty($search), function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
            ->orderBy('id', 'DESC');

        return DataTables::of($categories)
            ->addColumn('checkbox', function ($item) {
                return ' <input type="checkbox" id="chk-' . $item->id . '"
                                       class="form-check-input row-tic tic-check" name="check" value="' . $item->id . '"
                                       data-id="' . $item->id . '">';
            })
            ->addColumn('name', function ($item) {
                return $item->name;
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 0) {
                    return '<span class="badge bg-soft-danger text-danger"><span class="legend-indicator bg-danger"></span> ' . 'Inactive' . '</span>';
                } else {
                    return '<span class="badge bg-soft-success text-success"><span class="legend-indicator bg-success"></span> ' . 'Active' . '</span>';
                }
            })
            ->addColumn('created-date', function ($item) {
                return dateTime($item->created_at, 'd M Y h:i A');
            })
            ->addColumn('action', function ($item) {
                $editUrl = route('admin.product.categories.edit', $item->id);
                $deleteUrl = route('admin.product.categories.destroy', $item->id);
                return '<div class="btn-group" role="group">
                            <a class="btn btn-white btn-sm" href="'. $editUrl .'">
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
            ->rawColumns(['checkbox', 'name', 'status', 'created-date', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('category_image')) {
                try {
                    $image = $this->fileUpload($request->category_image, config('filelocation.product_category.path'), null, null, 'webp', 99);
                    if ($image) {
                        $categoryImage = $image['path'];
                        $driver = $image['driver'];
                    }
                } catch (\Exception $exp) {
                    return back()->with('error', 'Image could not be uploaded.');
                }
            }

            $category = new Category();
            $category->name = html_entity_decode($request->name);
            $category->slug = str::slug($category->name, '-');
            $category->status = $request->status;
            $category->category_image = $categoryImage;
            $category->category_image_driver = $driver;
            $category->save();
            throw_if(!$category, 'Something went wrong while storing category data. Please try again later.');

            DB::commit();
            return redirect()->route('admin.product.categories')->with('success', 'category save successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $data['category'] = Category::where('id', $id)->firstOr(function () {
                throw new \Exception('No category data found.');
            });
            return view('admin.category.edit', $data);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:categories,name,'.$id,
        ]);
        try {
            $category = Category::where('id', $id)->firstOr(function () {
                throw new \Exception('No category data found.');
            });

            if ($request->hasFile('category_image')) {
                $image = $this->fileUpload($request->category_image, config('filelocation.product_category.path'), null, null, 'webp', 99, $category->category_image, $category->category_image_driver);
                throw_if(empty($image), 'Image could not be uploaded');
            }

            $category->name = html_entity_decode($request->name);
            $category->slug = str::slug($category->name, '-');
            $category->status = $request->status;
            $category->category_image = $image['path'] ?? $category->category_image;
            $category->category_image_driver = $image['driver'] ?? $category->category_image_driver;
            $category->update();

            throw_if(!$category, 'Something went wrong while storing category data. Please try again later.');
            return redirect()->route('admin.product.categories')->with('success', 'category update successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $category = Category::with(['subcategories.details', 'subcategories.products.media', 'subcategories.products.variants_price'])
                ->findOrFail($id);
            foreach ($category->subcategories as $subcategory) {
                $subcategory->details()->delete();
                foreach ($subcategory->products as $product) {
                    $product->media()->delete();
                    $product->variants_price()->delete();
                    $product->delete();
                }
                $subcategory->delete();
            }
            $category->delete();
            DB::commit();
            return redirect()->back()->with('success', 'category deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
    public function destroyMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any Category.');
            return response()->json(['error' => 1]);
        } else {
            DB::transaction(function () use ($request) {
                Category::with('subcategories','subcategories.details')->whereIn('id', $request->strIds)->get()->map(function ($category){
                    foreach ($category->subcategories as $subcategory){
                        $this->fileDelete($subcategory->details->subcategory_image_driver, $subcategory->details->subcategory_image);
                        $subcategory->details->delete();
                        $subcategory->delete();
                    }
                    $this->fileDelete($category->category_image_driver, $category->category_image);
                    $category->delete();
                });
            });
            session()->flash('success', 'Category has been deleted successfully');
            return response()->json(['success' => 1]);
        }
    }
}

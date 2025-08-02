<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SubCategoryController extends Controller
{
    use Upload;
    public function index()
    {
        return view('admin.sub_category.index');
    }
    public function subCategoriesSearch(Request $request)
    {
        $search = @$request->search['value'];
        $subcategories = SubCategory::query()->with(['category'])
            ->when(!empty($search),function ($query) use ($search){
                $query->where('name','LIKE',"%{$search}%")
                    ->orWhere('name','LIKE',"%{$search}%");
            })
            ->orderBy('id', 'DESC');

        return DataTables::of($subcategories)
            ->addColumn('no', function ($item) {
                static $counter = 0;
                $counter++;
                return $counter;
            })
            ->addColumn('category-name', function ($item) {
                return optional($item->category)->name;
            })
            ->addColumn('sub-category-name', function ($item) {
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
                $editUrl = route('admin.product.subcategories.edit',$item->id);
                $deleteUrl = route('admin.product.subcategories.destroy', $item->id);
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
            ->rawColumns(['no', 'category-name','sub-category-name', 'status', 'created-date', 'action'])
            ->make(true);
    }

    public function create()
    {
        $data['categories'] = Category::all();
        return view('admin.sub_category.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required','unique:sub_categories'],
            'category_id' => 'required|exists:categories,id',
        ]);

        DB::beginTransaction();
        try {


            if ($request->hasFile('subcategory_image')) {
                try {
                    $image = $this->fileUpload($request->subcategory_image, config('filelocation.product_subcategory.path'), null, null, 'webp', 99);
                    if ($image) {
                        $subcategoryImage = $image['path'];
                        $driver = $image['driver'];
                    }
                } catch (\Exception $exp) {
                    return back()->with('error', 'Image could not be uploaded.');
                }
            }

            $subcategory = new Subcategory();
            $subcategory->category_id = $request->category_id;
            $subcategory->status = $request->status;
            $subcategory->name = $request->name;
            $subcategory->slug = Str::slug($request->name);
            $subcategory->image = $subcategoryImage ?? null;
            $subcategory->driver = $driver ?? null;
            $subcategory->save();

            DB::commit();
            return redirect()->route('admin.product.subcategories')->with('success', 'sub-category save successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $data['categories'] = Category::all();
            $data['subcategory'] = SubCategory::where('id', $id)->firstOr(function () {
                throw new \Exception('No sub-category data found.');
            });
            return view('admin.sub_category.edit', $data);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', Rule::unique('sub_categories')->ignore($id)],
            'category_id' => 'required|exists:categories,id',
        ]);

        DB::beginTransaction();
        try {
            $subcategory = SubCategory::where('id', $id)->firstOr(function () {
                throw new \Exception('No sub-category data found.');
            });

            if ($request->hasFile('subcategory_image')) {
                $image = $this->fileUpload($request->subcategory_image, config('filelocation.product_subcategory.path'), null, null, 'webp', 99, $subcategory->image??null, $subcategory->driver??null);
                throw_if(empty($image), 'Image could not be uploaded');
            }

            $subcategory->category_id = $request->category_id;
            $subcategory->status = $request->status;
            $subcategory->name = $request->name;
            $subcategory->slug = Str::slug($request->name);
            $subcategory->image = $image['path'] ?? $subcategory->image;
            $subcategory->driver = $image['driver'] ?? $subcategory->driver;
            $subcategory->save();

            DB::commit();
            return redirect()->route('admin.product.subcategories')->with('success', 'sub-category update successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $subcategory = SubCategory::findOrFail($id);
            $subcategory->delete();
            return redirect()->back()->with('success', 'sub-category deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

}

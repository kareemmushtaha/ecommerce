<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MainCategoryRequest;
use Illuminate\Http\Request;
use App\Models\MainCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use League\Flysystem\Config;
use mysql_xdevapi\Exception;


class MainCategoriesController extends Controller
{
    public function index()
    {

        /* this function { get_default_lang }  in config autoload*/
        $default_lang = get_default_lang();
        $categories = MainCategory::where('translation_lang', $default_lang)->selection()->get();

        return view('admin.mainCategories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.mainCategories.create');
    }

    public function store(MainCategoryRequest $request)
    {

        try {
            /* Transformation the data from jason to collection */

            $main_categories = collect($request->category);

            /* filter data in array  get abbr == arabic default value*/

            $filter = $main_categories->filter(function ($value, $key) {
                return $value['abbr'] == get_default_lang();
            });


            /* get all data where abbr == default value   */
            $default_category = array_values($filter->all()) [0];

            $filePath = "";
            if ($request->has('photo')) {

                $filePath = uploadImage('maincategories', $request->photo);
            }

            DB::beginTransaction();
            /* save the one object where abbr==default language  */
            if (!$request->has('category.0.     active'))
                $request->request->add(['active' => 0]);
            else
                $request->request->add(['active' => 1]);
            $default_category_id = MainCategory::insertGetId([
                'translation_lang' => $default_category['abbr'],
                'translation_of' => 0,
                'name' => $default_category['name'],
                'slug' => $default_category['name'],
                'photo' => $filePath,
                'active' => $request->active,

            ]);
            /* now get all object where 'abbr'  != default language */
            $categories = $main_categories->filter(function ($value, $key) {
                return $value['abbr'] != get_default_lang();
            });

            /* Enter all  object not default value  in array and the next save */


            if (isset($categories) && $categories->count()) {

                $categories_arr = [];
                foreach ($categories as $category) {
                    $categories_arr[] = [
                        'translation_lang' => $category['abbr'],
                        'translation_of' => $default_category_id,
                        'name' => $category['name'],
                        'slug' => $category['name'],

                        'photo' => $filePath
                    ];
                }
                /* dont use function create because this is  array */

                MainCategory::insert($categories_arr);
            }

            DB::commit();
            return redirect()->route('admin.MainCategories')->with(['success' => 'تم الحفظ بنجاح']);

        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->route('admin.MainCategories')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }
    }

    public function edit($mainCat_id)
    {
        /* get category with relation name "categories" */
        $mainCategory = MainCategory::with('categories')->selection()->find($mainCat_id);

        if (!$mainCategory) {
            return redirect()->route('admin.MainCategories')->with(['error' => 'هذا القسم غير موجود ']);
        }
        return view('admin.mainCategories.edit', compact('mainCategory'));


    }

    public function update($mainCat_id, MainCategoryRequest $request)
    {
        try {
            $main_category = MainCategory::find($mainCat_id);
            if (!$main_category)
                return redirect()->route('admin.maincategories')->with(['error' => 'هذا القسم غير موجود ']);

            // update date

            $category = array_values($request->category) [0];

            if (!$request->has('category.0.active'))
                $request->request->add(['active' => 0]);
            else
                $request->request->add(['active' => 1]);

            MainCategory::where('id', $mainCat_id)
                ->update([
                    'name' => $category['name'],
                    'active' => $request->active,
                ]);

            /*if user dont updated the photo he will take  the old image  and if user update image will save new image in database*/
            if ($request->has('photo')) {
                $filePath = uploadImage('maincategories', $request->photo);
                MainCategory::where('id', $mainCat_id)
                    ->update([
                        'photo' => $filePath,
                    ]);
            }
            return redirect()->route('admin.MainCategories')->with(['success' => 'تم ألتحديث بنجاح']);
        } catch (\Exception $ex) {
            return redirect()->route('admin.MainCategories')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }
    }

    public function destroy($id)
    {
        try {
            $mainCategory = MainCategory::find($id);
            if (!$mainCategory) {
                return redirect()->route('admin.MainCategories')->with(['error' => 'هذا القسم  غير موجود حاليا او ربما تم حذفه مسبقاً']);
            }
            /* this is relation between table mainCategory and table vendors  */
            $vendors = $mainCategory->vendors();
            if (isset($vendors) && $vendors->count() > 0) {
                return redirect()->route('admin.MainCategories')->with(['error' => 'لا يمكنك حذف هذا القسم لأنه قد ينضم إليه عدد من التجار  قم بالذهاب لإلغاء التفعيل']);
            }

            /*start delete the img on server */
            $image = Str::after($mainCategory->photo, 'assets/');
            $image = base_path('assets/' . $image);
            unlink($image);
            /* finish delete the img on server */

            /* delete all language translate to category */
            $mainCategory->categories()->delete();

            /* delete main language to category */

            $mainCategory->delete();
            return redirect()->route('admin.MainCategories')->with(['success' => 'تم حذف القسم بنجاح ']);

        } catch (Exception $ex) {
            return redirect()->route('admin.MainCategories')->with(['error' => 'حدث خلل ما اعد المحاولة فيما بعد ']);

        }
    }

    public function changeStatus($id)
    {
        try {
            $mainCategory = MainCategory::find($id);
            if (!$mainCategory) {
                return redirect()->route('admin.MainCategories')->with(['error' => 'هذا القسم  غير موجود حاليا او ربما تم حذفه مسبقاً']);
            }
            $status = $mainCategory->active == 0 ? 1 : 0;
            $mainCategory->update(['active' => $status]);
            return redirect()->route('admin.MainCategories')->with(['success' => 'تم تغيير حالة القسم بنجاح ']);


        } catch (Exception $ex) {
            return redirect()->route('admin.MainCategories')->with(['error' => 'حدث خلل ما اعد المحاولة فيما بعد ']);

        }
    }

}

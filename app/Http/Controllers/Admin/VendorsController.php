<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorRequest;
use Illuminate\Http\Request;
use App\Models\MainCategory;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VendorCreated;
use Illuminate\Support\Str;
use League\Flysystem\Config;
use mysql_xdevapi\Exception;


class VendorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vendors = Vendor::selection()->paginate(PAGINATION_COUNT);
        return view('admin.vendors.index', compact('vendors'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        /*جيب التصنيفات المفعلة والي هي اساسية اللغة */
        /* get category just (active && main category)   ( 'active' &&  'translation_of'= 0)   */
        $categories = MainCategory::where('translation_of', 0)->active()->get();
        return view('admin.vendors.create', compact('categories'));
    }


    public function store(VendorRequest $request)
    {
        try {

            if (!$request->has('active'))
                $request->request->add(['active' => 0]);
            else
                $request->request->add(['active' => 1]);

            $filePath = "";
            if ($request->has('logo')) {
                $filePath = uploadImage('vendors', $request->logo);
            }

            $vendor = Vendor::create([
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'active' => $request->active,
                'address' => $request->address,
                'logo' => $filePath,
                'password' => $request->password,
                'category_id' => $request->category_id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            Notification::send($vendor, new VendorCreated($vendor));

            return redirect()->route('admin.vendors')->with(['success' => 'تم الحفظ بنجاح']);

        } catch (\Exception $ex) {
//            return ($ex);
            return redirect()->route('admin.vendors')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);

        }
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        try {

            $vendor = Vendor::Selection()->find($id);
            /* if vendor not found */
            if (!$vendor)
                return redirect()->route('admin.vendors')->with(['error' => 'هذا المتجر غير موجود او ربما يكون محذوفا ']);

            $categories = MainCategory::where('translation_of', 0)->active()->get();
            return view('admin.vendors.edit', compact('vendor', 'categories'));

        } catch (\Exception $exception) {
            return redirect()->route('admin.vendors')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }
    }

    public function update($id, VendorRequest $request)
    {

        try {

            $vendor = Vendor::Selection()->find($id);
            if (!$vendor)
                return redirect()->route('admin.vendors')->with(['error' => 'هذا المتجر غير موجود او ربما يكون محذوفا ']);


            DB::beginTransaction();

            // first operation update to  logo
            if ($request->has('logo')) {
                $filePath = uploadImage('vendors', $request->logo);
                Vendor::where('id', $id)
                    ->update([
                        'logo' => $filePath,
                    ]);
            }


            if (!$request->has('active'))
                $request->request->add(['active' => 0]);
            else
                $request->request->add(['active' => 1]);


            $data = $request->except('_token', 'id', 'logo', 'password');

            if ($request->has('password') && !is_null($request->password)) {

                $data['password'] = $request->password;
            }

            // second operation update
            Vendor::where('id', $id)
                ->update(
                    $data
                );

            DB::commit();
            return redirect()->route('admin.vendors')->with(['success' => 'تم التحديث بنجاح']);
        } catch (\Exception $exception) {
            return $exception;
            DB::rollback();
            return redirect()->route('admin.vendors')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $vendor = Vendor::find($id);
            if (!$vendor) {
                return redirect()->route('admin.vendors')->with(['error' => 'هذا المتجر    غير موجود حاليا او ربما تم حذفه مسبقاً']);
            }

            $image = Str::after($vendor->logo, 'assets/');
            $image = base_path('assets/' . $image);
            unlink($image);
            /* finish delete the img on server */


            /* delete main language to category */

            $vendor ->delete();
            return redirect()->route('admin.vendors')->with(['success' => 'تم حذف المتجر  بنجاح ']);


        } catch (Exception $ex) {
            return redirect()->route('admin.vendors')->with(['error' => 'حدث خلل ما اعد المحاولة فيما بعد ']);

        }
    }


    public function changeStatus($id)
    {
        try {
            $vendors = Vendor::find($id);
            if (!$vendors) {
                return redirect()->route('admin.vendors')->with(['error' => 'هذا المتجر  غير موجود حاليا او ربما تم حذفه مسبقاً']);
            }
            $status = $vendors->active == 0 ? 1 : 0;
            $vendors->update(['active' => $status]);
            return redirect()->route('admin.vendors')->with(['success' => 'تم تغيير حالة المتجر بنجاح ']);


        } catch (Exception $ex) {
            return redirect()->route('admin.vendors')->with(['error' => 'حدث خلل ما اعد المحاولة فيما بعد ']);

        }


    }

}

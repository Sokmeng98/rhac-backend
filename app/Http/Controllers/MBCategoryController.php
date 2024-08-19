<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MB_Category;
use App\Http\Resources\MBCategoryResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class MBCategoryController extends Controller
{
    public function createMBCategory(Request $request)
    {
        $title_validator = Validator::make(
            $request->all(),
            [
                'name' => 'unique:m_b__categories|nullable',
                'name_en' => 'unique:m_b__categories|nullable',
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'img' => 'sometimes|mimes:jpg,jpeg,png,webp|file'
            ]
        );
        if ($title_validator->fails()) {
            return response(['message' => $title_validator->errors()], 409);
        }
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        if ($request->name == null && $request->name_en == null) {
            return response(['message' => "Either title khmer or english should not be empty!"], 400);
        }
        $mb_img = "";
        if ($request->img) {
            $mb_img = Storage::put('public/mb_type', $request->img);
        }
        $mbcategory = MB_Category::create([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'slug_en' => Str::lower(str_replace(' ', '-', $request->name_en)),
            'slug_kh' => Str::lower(urlencode(str_replace(' ', '-', $request->name))),
            'img' => ltrim($mb_img, 'public'),
        ]);
        return response(['data' => $mbcategory], 201);
    }

    public function deleteMBCategory($id)
    {
        $category = MB_Category::find($id);
        if (!$category) {
            return response()->json(['message' => '404 Not Found'], 404);
        }
        if (Storage::exists('public' . $category->image)) {
            Storage::delete('public' . $category->image);
        }
        $category->delete();
        return response()->json([
            'message' => 'Method Bank Category has been deleted successfully.'
        ]);
    }

    public function showMBCategory(MB_Category $mbcategory, Request $request)
    {
        if (empty($request['limit']) || is_numeric($request['limit'])) {
            $mb_categories_data = MBCategoryResource::collection($mbcategory->with(['m_b__subcategories'])->paginate($request['limit']));
            if ((int)$request['page'] > $mb_categories_data->lastPage()) {
                return response(['message' => 'Page index out of range']);
            }
            return $mb_categories_data;
        } else {
            return response(['message' => 'Provided data is invalid.'], 400);
        }
    }

    public function updateMBCategory(Request $request, $id)
    {
        if (!MB_Category::find($id)) {
            return response(['message' => '404 Not Found'], 404);
        }
        $title_validator = Validator::make(
            $request->all(),
            [
                'name' => 'unique:m_b__categories',
                'name_en' => 'unique:m_b__categories',
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'img' => 'sometimes|mimes:jpg,jpeg,png,webp|file'
            ]
        );
        if (empty($request->all())) {
            return response(['message' => 'No Data Change']);
        }
        if ($title_validator->fails()) {
            return response(['message' => $title_validator->errors()], 409);
        }
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $data = $request->all();
        $mbcategory = MB_Category::where('id', $id)->firstOrFail();
        if ($request->img) {
            $mbcategory_img = Storage::put('public/mb_type', $request->img);
            $data['img'] = ltrim($mbcategory_img, 'public');
            Storage::delete('public' . $mbcategory->img);
        }
        if ($request->name_en) {
            $data['slug_en'] = Str::lower(str_replace(' ', '-', $request->name_en));
        }
        if ($request->name) {
            $data['slug_kh'] = Str::lower(urlencode(str_replace(' ', '-', $request->name)));
        }
        $mbcategory->fill($data);
        $mbcategory->save();
        return MBCategoryResource::collection($mbcategory->where('id', $id)->get())->response();
    }

    public function showMBCategoryById($id)
    {
        $data = MB_Category::where('id', $id)->with(['m_b__subcategories'])->get();
        if (count($data) === 0) {
            return response(['message' => '404 Not Found'], 404);
        }
        return MBCategoryResource::collection($data)->response();
    }
}

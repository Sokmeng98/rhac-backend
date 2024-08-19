<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MB_Subcategory;
use App\Http\Resources\MBSubcategoryResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MBSubcategoryController extends Controller
{
    public function createMBSubcategory(Request $request)
    {
        $title_validator = Validator::make(
            $request->all(),
            [
                'name' => 'unique:m_b__subcategories|nullable',
                'name_en' => 'unique:m_b__subcategories|nullable',
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'img' => 'sometimes|mimes:jpg,jpeg,png,webp|file',
                'm_b__categories_id' => 'required|exists:m_b__categories,id'
            ]
        );
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        if ($title_validator->fails()) {
            return response(['message' => $title_validator->errors()], 409);
        }
        if ($request->name == null && $request->name_en == null) {
            return response(['message' => "Either title khmer or english should not be empty!"], 400);
        }
        $mb_img = "";
        if ($request->img) {
            $mb_img = Storage::put('public/mb_type', $request->img);
        }
        $mbsubcategory = MB_Subcategory::create([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'slug_en' => Str::lower(str_replace(' ', '-', $request->name_en)),
            'slug_kh' => Str::lower(urlencode(str_replace(' ', '-', $request->name))),
            'm_b__categories_id' => $request->m_b__categories_id,
            'img' => ltrim($mb_img, 'public')
        ]);
        return response(['data' => $mbsubcategory], 201);
    }

    public function updateMBSubcategory(Request $request, $id)
    {
        $mbsubcategory = MB_Subcategory::find($id);
        if (!$mbsubcategory) {
            return response(['message' => '404 Not Found'], 404);
        }
        $title_validator = Validator::make(
            $request->all(),
            [
                'name' => 'unique:m_b__subcategories|nullable',
                'name_en' => 'unique:m_b__subcategories|nullable'
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'img' => 'sometimes|mimes:jpg,jpeg,png,webp|file',
                'm_b__categories_id' => 'sometimes|exists:m_b__categories,id'
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
        $data = $request->all();
        if ($request->img) {
            $mbsubcategory_img = Storage::put('public/mb_type', $request->img);
            $data['img'] = ltrim($mbsubcategory_img, 'public');
            Storage::delete('public' . $mbsubcategory);
        }
        if ($request->name_en) {
            $data['slug_en'] = Str::lower(str_replace(' ', '-', $request->name_en));
        }
        if ($request->name) {
            $data['slug_kh'] = Str::lower(urlencode(str_replace(' ', '-', $request->name)));
        }
        $mbsubcategory->fill($data);
        $mbsubcategory->save();
        return MBSubcategoryResource::collection($mbsubcategory->where('id', $id)->get()->response());
    }

    public function deleteMBSubcategory($id)
    {
        $mbsubcategory = MB_Subcategory::find($id);
        if (!$mbsubcategory) {
            return response(['message' => '404 Not Found'], 404);
        }
        if (Storage::exists('public' . $mbsubcategory->image)) {
            Storage::delete('public' . $mbsubcategory->image);
        }
        $mbsubcategory->delete();
        return response()->json([
            'message' => 'Method Bank Subcategory has been deleted successfully.'
        ]);
    }

    public function getMBSubcategory(MB_Subcategory $mb_subcategory, Request $request)
    {
        return MBSubcategoryResource::collection($mb_subcategory->paginate($request['limit']));
    }

    public function getMBSubcategoryById($id)
    {
        $mbsubcategory = MB_Subcategory::find($id);
        if (!$mbsubcategory) {
            return response(['message' => '404 Not Found'], 404);
        }
        return MB_Subcategory::collection($mbsubcategory)->response();
    }
}

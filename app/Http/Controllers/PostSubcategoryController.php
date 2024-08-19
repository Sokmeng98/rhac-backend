<?php

namespace App\Http\Controllers;

use App\Models\Post_Category;
use Illuminate\Http\Request;
use App\Models\Post_Subcategory;
use App\Http\Resources\PostSubcategoryResource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PostSubcategoryController extends Controller
{
    public function createPostSubcategory(Request $request)
    {
        $title_validator = Validator::make(
            $request->all(),
            [
                'name' => 'nullable|unique:post__subcategories',
                'name_en' => 'nullable|unique:post__subcategories',
            ],
        );
        $validator = Validator::make(
            $request->all(),
            [
                'post__categories_id' => 'required|exists:post__categories,id'
            ],
            [
                'post__categories_id.required' => 'Post category id field is required.',
                'post__categories_id.exists' => 'Invalid Post Category Id'
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
        };
        $posttype = Post_Subcategory::create([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'post__categories_id' => $request->post__categories_id,
            'slug_en' => Str::lower(str_replace(' ', '-', $request->name_en)),
            'slug_kh' => Str::lower(urlencode(str_replace(' ', '-', $request->name))),
        ]);
        return response([
            'data' => $posttype
        ], 201);
    }

    public function deletePostSubcategory($id)
    {
        if (count(Post_Subcategory::where('id', $id)->get()) == 0) {
            return response()->json(['message' => '404 Not Found'], 404);
        }
        Post_Subcategory::where('id', $id)->delete();
        return response()->json([
            'message' => 'Post Subcategory has been deleted successfully.'
        ], 200);
    }

    public function updatePostSubcategory(Request $request, $id)
    {
        if (!Post_Subcategory::find($id)) {
            return response(['message' => '404 Not Found'], 404);
        }
        $title_validator = Validator::make(
            $request->all(),
            [
                'name' => 'sometimes|unique:post__subcategories',
                'name_en' => 'sometimes|unique:post__subcategories',
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'post__categories_id' => 'sometimes|required|exists:post__categories,id'
            ],
            [
                'post__categories_id.required' => 'Post category id field is required.',
                'post__categories_id.exists' => 'Invalid Post Category Id'
            ]
        );
        if ($title_validator->fails()) {
            return response(['message' => $title_validator->errors()], 409);
        }
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $data = $request->all();
        $posttype = Post_Subcategory::where('id', $id)->firstOrFail();
        if ($request->name_en) {
            $data['slug_en'] = Str::lower(str_replace(' ', '-', $request->name_en));
        }
        if ($request->name) {
            $data['slug_kh'] = Str::lower(urlencode(str_replace(' ', '-', $request->name)));
        }
        $posttype->fill($data);
        $posttype->save();
        return PostSubcategoryResource::collection($posttype->where('id', $id)->get())->response();
    }

    public function getPostSubcategoryByPostCategory(Request $request, Post_Subcategory $posttype, Post_Category $post_Main_Type)
    {
        $checkposttype = $post_Main_Type->newQuery()->where(function ($query) use ($request) {
            $query->where('slug_kh', 'LIKE', '%' . Str::lower(urlencode($request->input('type'))) . '%');
            $query->orWhere('slug_en', 'LIKE', '%' . $request->input('type') . '%');
        });
        if (count($checkposttype->get()) == 0 && $request->input('type') != '') {
            abort(404);
        }
        $posttype = $posttype->newQuery();
        $posttype->whereHas('post__categories', function ($query) use ($request) {
            $query->where('post__categories.slug_kh', 'LIKE', '%' . Str::lower(urlencode($request->input('type'))) . '%');
            $query->orWhere('post__categories.slug_en', 'LIKE', '%' . $request->input('type') . '%');
        });
        $posttype = $posttype->with(['post__categories']);
        return PostSubcategoryResource::collection($posttype->paginate($request['limit']));
    }

    public function showPostSubcategoryById($id)
    {
        $data = Post_Subcategory::where('id', $id)->get();
        if ($data->count() === 0) {
            return response(['message' => '404 Not Found'], 404);
        };
        return PostSubcategoryResource::collection($data)->response();
    }
}

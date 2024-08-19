<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post_Category;
use App\Http\Resources\PostCategoryResource;
use Illuminate\Support\Str;
use App\Models\Post_Subcategory;
use Illuminate\Support\Facades\Validator;

class PostCategoryController extends Controller
{
    public function createPostCategory(Request $request)
    {
        $title_validator = Validator::make(
            $request->all(),
            [
                'name' => 'nullable|unique:post__categories',
                'name_en' => 'nullable|unique:post__categories',
            ]
        );
        if ($title_validator->fails()) {
            return response([
                'message' => $title_validator->errors()
            ], 409);
        }
        if ($request->name == null && $request->name_en == null) {
            return response([
                'message' => "Either title khmer or english should not be empty!"
            ], 400);
        }
        $postmaintype = Post_Category::create([
            'name' => $request->name,
            'name_en' => $request->name_en,
            'slug_en' => Str::lower(str_replace(' ', '-', $request->name_en)),
            'slug_kh' => Str::lower(urlencode(str_replace(' ', '-', $request->name))),
        ]);
        return response([
            'data' => $postmaintype
        ], 201);
    }

    public function deletePostCategory($id, Post_Subcategory $post_type)
    {
        $posttype = $post_type->newQuery();
        $posttype->whereHas('post__categories', function ($query) use ($id) {
            $query->where('post__categories.id', $id);
        });
        if (count(Post_Category::where('id', $id)->get()) === 0) {
            return response([
                'message' => '404 Not Found'
            ], 404);
        };
        if (count($posttype->get()) != 0) {
            return response()->json([
                "message" => "This post category cannot be deleted."
            ], 422);
        };
        Post_Category::where('id', $id)->delete();
        return response()->json([
            'message' => 'Post category has been deleted successfully.'
        ]);
    }

    public function showPostCategory(Post_Category $postmaintype, Request $request)
    {
        $post_maintype = $postmaintype->with(["post__subcategories"]);
        if ($request->input('limit') === 'all') {
            return PostCategoryResource::collection($post_maintype->get());
        } elseif (is_numeric($request['limit']) || empty($request['limit'])) {
            return PostCategoryResource::collection($post_maintype->paginate($request['limit']));
        } else {
            return response([
                'message' => 'Provided Data is invalid.'
            ], 400);
        }
    }

    public function updatePostCategory(Request $request, $id)
    {
        if (!Post_Category::find($id)) {
            return response(['message' => '404 Not Found'], 404);
        }
        $title_validator = Validator::make(
            $request->all(),
            [
                'name' => 'sometimes|unique:post__categories',
                'name_en' => 'sometimes|unique:post__categories',
            ]
        );
        if ($title_validator->fails()) {
            return response(['message' => $title_validator->errors()], 409);
        }
        $data = $request->all();
        $postmaintype = Post_Category::where('id', $id)->firstOrFail();
        if ($request->name_en) {
            $data['slug_en'] = Str::lower(str_replace(' ', '-', $request->name_en));
        }
        if ($request->name) {
            $data['slug_kh'] = Str::lower(urlencode(str_replace(' ', '-', $request->name)));
        }
        $postmaintype->fill($data);
        $postmaintype->save();
        return PostCategoryResource::collection($postmaintype->where('id', $id)->get())->response();
    }

    public function showPostCategoryById($id)
    {
        $data = Post_Category::where('id', $id)->get();
        if ($data->count() === 0) {
            return response(['message' => '404 Not Found'], 404);
        };
        return PostCategoryResource::collection($data)->response();
    }
}

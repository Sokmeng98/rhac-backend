<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Post_Category;
use App\Models\Post_Subcategory;
use App\Models\MB_Category;
use App\Models\MB_Subcategory;
use Illuminate\Http\Request;
use App\Http\Resources\VideoResource;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VideoController extends Controller
{
    public function createVideo(Request $request)
    {
        $current = ($request->post_date) ? $request->post_date : Carbon::now();
        $title_validator =  Validator::make(
            $request->all(),
            [
                'title_en' => 'unique:videos',
                'title_kh' => 'unique:videos'
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'video_url' => 'required',
                'post' => 'nullable|array',
                'post.*.category' => 'exists:post__categories,id',
                'm_b_categories' => 'array',
                'm_b_categories.*.m_b__categories_id' => 'exists:m_b__categories,id',
                'm_b_categories.*.m_b__subcategories_id' => 'exists:m_b__subcategories,id',
                'mb_professional' => 'nullable|array',
                'mb_professional.*' => 'in:Grade 5&6,Grade 7-9,Grade 10-12',
                'post_date' => 'date_format:Y-m-d H:i:s'
            ],
            [
                'm_b__categories.*.m_b__categories_id.exists' => 'Invalid method bank category id',
                'm_b__categories.*.m_b__subcategories_id.exists' => 'Invalid method bank subcategory id',
            ]
        );
        if ($title_validator->fails()) {
            return response(['message' => $title_validator->errors()], 409);
        }
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        if ($request->title_kh == null && $request->title_en == null) {
            return response(['message' => 'Either title khmer or english should not be empty!'], 400);
        }
        $postCategoryId = [];
        $postSubcategoryId = [];
        if ($request->post) {
            foreach ($request->post as $item) {
                $category_id = $item['category'];
                $subcategory_id = null;
                if (!isset($item['subcategory'])) {
                    $post_subtype = new Post_Subcategory;
                    $postsubtype = $post_subtype->newQuery();
                    $postsubtype->whereHas('post__categories', function ($query) use ($category_id) {
                        $query->where('post__categories.id', $category_id);
                    });
                    if (count($postsubtype->get()) !== 0) {
                        return response()->json([
                            "message" => (["message" => ["The post category id: " . $category_id . " requires subcategory."]])
                        ], 400);
                    }
                }
                if (isset($item['subcategory'])) {
                    $subcategory_id = $item['subcategory'];
                    $post_subcategory = Post_Subcategory::where('id', $subcategory_id)
                        ->where('post__categories_id', $category_id)->get();
                    if (count($post_subcategory) === 0) {
                        return response(['message' => "Post Category does not contain this subcategory."], 400);
                    }
                }
                $postCategoryId[] = $category_id;
                $postSubcategoryId[] = $subcategory_id;
            }
        }
        $mbCategoryId = [];
        $mbSubcategoryId = [];
        if ($request->m_b__categories) {
            foreach ($request->m_b__categories as $item) {
                $category_id = $item['m_b__categories_id'];
                $subcategory_id = null;
                if (!isset($item['m_b__subcategories_id'])) {
                    $mb_subtype = new MB_Subcategory;
                    $mbsubtype = $mb_subtype->newQuery();
                    $mbsubtype->whereHas('m_b__categories', function ($query) use ($category_id) {
                        $query->where('m_b__categories_id', $category_id);
                    });
                    if (count($mbsubtype->get()) !== 0) {
                        return response()->json([
                            "message" => (["message" => ["The method bank category id: " . $category_id . " requires subcategory."]])
                        ], 400);
                    }
                }
                if (isset($item['m_b__subcategories_id'])) {
                    $subcategory_id = $item['m_b__subcategories_id'];
                    $mb_subcategory = MB_Subcategory::where('id', $subcategory_id)
                        ->where('m_b__categories_id', $category_id)->get();
                    if (count($mb_subcategory) === 0) {
                        return response(['message' => 'Method Bank Category does not contain this subcategory.'], 400);
                    }
                }
                $mbCategoryId[] = $category_id;
                $mbSubcategoryId[] = $subcategory_id;
            }
        }
        $mbProfessional = [];
        if ($request->mb_professional) {
            $mbProfessional = $request->mb_professional;
        }
        $video = Video::create([
            'title_kh' => $request->title_kh,
            'title_en' => $request->title_en,
            'video_url' => $request->video_url,
            'mb_professional' => $mbProfessional,
            'date' => $current
        ]);

        $videoData = Video::find($video->id);
        if (count($mbCategoryId) !== 0) {
            $mbCategory = MB_Category::whereIn('id', $mbCategoryId)->get();
            $videoData->m_b__categories()->attach($mbCategory);
        }
        if (count($mbSubcategoryId) !== 0) {
            $mbSubcategory = MB_Subcategory::whereIn('id', $mbSubcategoryId)->get();
            $videoData->m_b__subcategories()->attach($mbSubcategory);
        }
        if (count($postCategoryId) !== 0) {
            $postCategory = Post_Category::whereIn('id', $postCategoryId)->get();
            $videoData->post__categories()->attach($postCategory);
        }
        if (count($postSubcategoryId) !== 0) {
            $postSubcategory = Post_Subcategory::whereIn('id', $postSubcategoryId)->get();
            $videoData->post__subcategories()->attach($postSubcategory);
        }
        return response(['data' => $video], 201);
    }

    public function deleteVideo($id)
    {
        $video = Video::find($id);
        if (!$video) {
            return response(['message' => '404 Not Found'], 404);
        }
        Video::where('id', $id)->delete();
        return response()->json([
            'message' => 'Video has been deleted successfully.'
        ]);
    }

    public function showVideo(Video $video, Request $request)
    {
        $paramValidator = Validator::make(
            $request->all(),
            [
                'maintype' => 'in:mb_professional,mb_learner,post|nullable'
            ]
        );
        if ($paramValidator->fails()) {
            return response(['message' => $paramValidator->errors()]);
        }
        $data = $video->with(['post__categories', 'post__subcategories', 'm_b__categories', 'm_b__subcategories']);
        if ($request->has('maintype')) {
            $type = $request->input('maintype');
            switch ($type) {
                case 'post':
                    $data = $data->whereHas('post__categories');
                    break;
                case 'mb_professional':
                    $data = $data->where('mb_professional', '!=', '[]');
                    break;
                case 'mb_learner':
                    $data = $data->whereHas('m_b__categories');
                    break;
            }
        }
        if (!empty($request->input('type'))) {
            $type = $request->input('type');
            if ($type === 'Grade 5') {
                $type = 'Grade 5&6';
            }
            $data->whereHas('post__categories', function ($query) use ($type) {
                $query->where('post__categories.name', $type);
                $query->orWhere('post__categories.name_en', $type);
            })
                ->orWhereHas('post__subcategories', function ($query) use ($type) {
                    $query->where('post__subcategories.name', $type);
                    $query->orWhere('post__subcategories.name_en', $type);
                })
                ->orWhereHas('m_b__categories', function ($query) use ($type) {
                    $query->where('m_b__categories.name', $type);
                    $query->orWhere('m_b__categories.name_en', $type);
                })
                ->orWhereHas('m_b__subcategories', function ($query) use ($type) {
                    $query->where('m_b__subcategories.name', $type);
                    $query->orWhere('m_b__subcategories.name_en', $type);
                })
                ->orWhereJsonContains('mb_professional', $type);
        }
        if (!empty($request->title)) {
            $terms = explode(' ', $request['search']);
            $data->where(function ($query) use ($terms) {
                foreach ($terms as $term) {
                    return $query->orWhere('title', 'LIKE', '%' . $term . '%');
                }
            });
        }
        if (empty($request['limit']) || is_numeric($request['limit'])) {
            return VideoResource::collection($data->orderBy('id', 'desc')->paginate($request['limit']));
        } else {
            return response(['message' => 'Provided data is invalid'], 400);
        }
    }

    public function updateVideo(Request $request, $id)
    {
        $video = Video::find($id);
        if (!$video) {
            return response(['message' => '404 Not Found'], 404);
        }
        $title_validator = Validator::make(
            $request->all(),
            [
                'title_en' => 'unique:videos',
                'title_kh' => 'unique:videos'
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'video_url' => 'sometimes|required',
                'post.*.category' => 'sometimes|exists:post__categories,id',
                'm_b_categories' => 'sometimes|array',
                'm_b_categories.*.m_b__categories_id' => 'sometimes|exist:m_b__categories,id',
                'm_b_categories.*.m_b__subcategories_id' => 'sometimes|exist:m_b__subcategories,id',
                'mb_professional.*' => 'sometimes|in:Grade 5&6,Grade 7-9,Grade 10-12',
                'post_modified' => 'date_format:Y-m-d H:i:s'
            ],
            [
                'm_b__categories.*.m_b__categories_id.exists' => 'Invalid method bank category id',
                'm_b__categories.*.m_b__subcategories_id.exists' => 'Invalid method bank subcategory id',
            ]
        );
        if ($title_validator->fails()) {
            return response(['message' => $title_validator->errors()], 409);
        }
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        if ($request->has('title_kh') or $request->has('title_en')) {
            if ($request->title_kh == null && $request->title_en == null) {
                return response(['message' => 'Either title khmer or english should not be empty!'], 400);
            }
        }
        $current = ($request->post_modified) ? $request->post_modified : Carbon::now();
        $request['modified'] = $current;
        $postCategoryId = [];
        $postSubcategoryId = [];
        if ($request->post) {
            foreach ($request->post as $item) {
                $category_id = $item['category'];
                $subcategory_id = null;
                if (!isset($item['subcategory'])) {
                    $post_type = new Post_Subcategory;
                    $posttype = $post_type->newQuery();
                    $posttype->whereHas('post__categories', function ($query) use ($category_id) {
                        $query->where('post__categories.id', $category_id);
                    });
                    if (count($posttype->get()) != 0) {
                        return response()->json([
                            "message" => (["message" => ["The post category id:" . $category_id . " require subcategory."]])
                        ], 400);
                    }
                }
                if (isset($item['subcategory'])) {
                    $subcategory_id = $item['subcategory'];
                    $post_subcategory = Post_Subcategory::where('id', $subcategory_id)
                        ->where('post__categories_id', $category_id)->get();
                    if (count($post_subcategory) == 0) {
                        return response(['message' => "Post Category does not contain this subcategory."], 400);
                    }
                }
                $postCategoryId[] = $category_id;
                $postSubcategoryId[] = $subcategory_id;
            }

            $postCategory = Post_Category::whereIn('id', $postCategoryId)->get();
            $video->post__categories()->sync($postCategory);
            $postSubcategory = Post_Subcategory::whereIn('id', $postSubcategoryId)->get();
            $video->post__subcategories()->sync($postSubcategory);
        }
        if ($request->post === []) {
            $postCategory = Post_Category::whereIn('id', $postCategoryId)->get();
            $video->post__categories()->sync($postCategory);
            $postSubcategory = Post_Subcategory::whereIn('id', $postSubcategoryId)->get();
            $video->post__subcategories()->sync($postSubcategory);
        }
        $mbCategoryId = [];
        $mbSubcategoryId = [];
        if ($request->m_b__categories) {
            foreach ($request->m_b__categories as $item) {
                $category_id = $item['m_b__categories_id'];
                $subcategory_id = null;
                if (isset($item['m_b__subcategories_id'])) {
                    $subcategory_id = $item['m_b__subcategories_id'];
                    $mbSubType = MB_Subcategory::where('id', $subcategory_id)->where('m_b__categories_id', $category_id)->get();
                    if (count($mbSubType) === 0) {
                        return response(['message' => 'Method Bank Category does not contain this subcategory.'], 400);
                    }
                }
                if (!isset($item['m_b__subcategories_id'])) {
                    $mb_type = new MB_Category;
                    $mbtype = $mb_type->newQuery();
                    $mbtype->whereHas('m_b__categories', function ($query) use ($item) {
                        $query->where('m_b__categories_id', $item['m_b__categories_id']);
                    });
                    if (count($posttype->get()) != 0) {
                        return response()->json([
                            "message" => (["message" => ["The meethod bank category id:" . $category_id . " require subcategory."]])
                        ], 400);
                    }
                }
                $mbCategoryId[] = $category_id;
                $mbSubcategoryId[] = $subcategory_id;
            }
            $mbCategory = MB_Category::whereIn('id', $mbCategoryId)->get();
            $video->m_b__categories()->sync($mbCategory);
            $mbSubcategory = MB_Subcategory::whereIn('id', $mbSubcategoryId)->get();
            $video->m_b__subcategories()->sync($mbSubcategory);
        }
        if ($request->m_b__categories === []) {
            $mbCategory = MB_Category::whereIn('id', $mbCategoryId)->get();
            $video->m_b__categories()->sync($mbCategory);
            $mbSubcategory = MB_Subcategory::whereIn('id', $mbSubcategoryId)->get();
            $video->m_b__subcategories()->sync($mbSubcategory);
        }
        $data = $request->all();
        $video->fill($data);
        $video->save();
        return $video;
    }

    public function getVideoById($id)
    {
        $data = Video::find($id);
        if (!$data) {
            return response(['message' => '404 Not Found'], 404);
        }
        $data = $data->with(['post__categories', 'post__subcategories', 'm_b__categories', 'm_b__subcategories'])->find($id);
        return $data;
    }

    public function searchFilter(Request $request, Video $video)
    {
        $videoCheck = $video->newQuery();
        $video->where(function ($query) use ($request) {
            return $query->orWhere('title_en', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('title_kh', 'LIKE', '%' . $request->input('search') . '%');
        });
        if ($videoCheck->get()->count() === 0) {
            return response(['message' => 'No Content Match']);
        } else {
            return VideoResource::collection($videoCheck->orderBy('id', 'desc')->paginate($request['limit']));
        }
    }

    public function getVideoByType(Request $request)
    {
        $type = $request->input('type');
        $video = null;
        switch ($type) {
            case 'mb_learner':
            case 'mbLearner':
                $video = Video::where('m_b__categories_id', '!=', 'NULL');
                break;
            case 'mb_professional':
            case 'mbProfessional':
                $video = Video::where('mb_professional', '!=', 'NULL');
                break;
            case 'post':
                $video = Video::where('post__categories_id', '!=', 'NULL');
                break;
            default:
                return response(['message' => 'Provided data is invalid.'], 400);
        }
        return VideoResource::collection($video->orderBy('id', 'desc')->paginate($request['limit']));
    }
}

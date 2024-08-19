<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Http\Resources\PostResource;
use App\Http\Resources\VideoResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Post_Category;
use App\Models\Post_Subcategory;
use App\Models\MB_Learner;
use App\Models\MB_Professional;
use App\Models\Video;
use App\Traits\ImageTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class PostController extends Controller
{
    use ImageTrait;

    public function createPost(Request $request)
    {
        $post_type = new Post_Subcategory;
        $current = ($request->post_date) ? $request->post_date : Carbon::now();
        $title_validator = Validator::make(
            $request->all(),
            [
                'title_kh' => 'nullable|unique:posts',
                'title_en' => 'nullable|unique:posts'
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'content_kh' => 'required_with:title_kh',
                'content_en' => 'required_with:title_en',
                'image' => 'nullable|mimes:jpg,jpeg,png,webp|file',
                'pdf' => 'nullable|mimes:pdf|file',
                'post' => 'required',
                'post.*.category' => 'required|exists:post__categories,id',
                'post.*.subcategory' => 'sometimes|exists:post__subcategories,id',
                'post_date' => 'date_format:Y-m-d H:i:s'
            ],
            [
                'post.required' => 'Post Category id is required',
                'post.*.category.required' => 'Post Category id is required',
                'post.*.category.exists' => 'Invalid Post Category Id',
                'post.*.subcategory.exists' => 'Invalid Post Subcategory Id'
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
        $author = $request->author ?? 'RHAC';
        $excerpt_kh = strip_tags($request->content_kh);
        $excerpt_en = strip_tags($request->content_en);
        $excerpt_kh = Str::words($excerpt_kh, 10);
        $excerpt_en = Str::words($excerpt_en, 50);

        $baseSlugEn = Str::lower(str_replace(' ', '-', Str::limit($request->title_en, 30, $end = '')));
        $baseSlugKh = Str::lower(urlencode(str_replace(' ', '-', Str::limit($request->title_kh, 30, $end = ''))));
        $slugEnIdx = 1;
        $slugKhIdx = 1;
        $slug_en = $baseSlugEn;
        $slug_kh = $baseSlugKh;
        $postWithSlugEn = Post::where('slug_en', $slug_en)->get();
        $postWithSlugKh = Post::where('slug_kh', $slug_kh)->get();

        while (count($postWithSlugEn) > 0 || count($postWithSlugKh) > 0) {
            if (count($postWithSlugEn) > 0) {
                $slug_en = $baseSlugEn . '-' . $slugEnIdx;
                $slugEnIdx++;
                $postWithSlugEn = Post::where('slug_en', $slug_en)->get();
            }

            if (count($postWithSlugKh) > 0) {
                $slug_kh = $baseSlugKh . '-' . $slugKhIdx;
                $slugKhIdx++;
                $postWithSlugKh = Post::where('slug_kh', $slug_kh)->get();
            }
        }

        $post_img = '';
        if ($request->image) {
            $post_img = $this->imageResizeAndSave($request, 'post', 'post');
        }
        $post_pdf = '';
        if ($request->pdf) {
            $post_pdf = Storage::put('public/post', $request->pdf);
        }
        $status = $current > now() ? 'Scheduled' : 'Publish';
        $post = Post::create([
            'image' => $post_img,
            'title_kh' => $request->title_kh,
            'title_en' => $request->title_en,
            'content_kh' => $request->content_kh,
            'content_en' => $request->content_en,
            'excerpt_kh' => $excerpt_kh,
            'excerpt_en' => $excerpt_en,
            'pdf' => trim($post_pdf, 'public'),
            'tags' => $request->tags,
            'view' => 0,
            'author' => $author,
            'date' =>  $current,
            'users_id' => Auth::user()->id,
            'slug_en' => $slug_en,
            'slug_kh' => $slug_kh,
            'status' => $status,
        ]);
        $postCategoryId = [];
        $postSubcategoryId = [];
        $sameCategoryCheck = 0;
        foreach ($request->post as $item) {
            $category_id = $item['category'];
            $subcategory_id = null;
            $postmaintype = Post_Category::where('id', $item['category'])->firstOrFail();
            if ($sameCategoryCheck !== $postmaintype->id) {
                $postmaintype->post_count = $postmaintype->post_count + 1;
                $postmaintype->save();
                $sameCategoryCheck = $postmaintype->id;
            }
            if (isset($item['subcategory'])) {
                $subcategory_id = $item['subcategory'];
                $post_subcategory = Post_Subcategory::where('id', $item['subcategory'])->where('post__categories_id', $item['category'])->get();
                if (count($post_subcategory) === 0) {
                    return response(['message' => "Post Category does not contain this subcategory."], 400);
                } else {
                    $postsubtype = Post_Subcategory::where('id', $item['subcategory'])->firstOrFail();
                    $postsubtype->post_count = $postsubtype->post_count + 1;
                    $postsubtype->save();
                }
            }
            if (!isset($item['subcategory'])) {
                $posttype = $post_type->newQuery();
                $posttype->whereHas('post__categories', function ($query) use ($item) {
                    $query->where('post__categories.id', $item['category']);
                });
                if (count($posttype->get()) != 0) {
                    return response()->json([
                        "message" => (['post__subcategories_id' => ['The post subcategory id field is required.']])
                    ], 400);
                }
            }
            $postCategoryId[] = $category_id;
            $postSubcategoryId[] = $subcategory_id;
        }
        $postData = Post::find($post->id);
        $postCategory = Post_Category::whereIn('id', $postCategoryId)->get();
        $postData->post__categories()->attach($postCategory);
        if (count($postSubcategoryId) !== 0) {
            $postSubcategory = Post_Subcategory::whereIn('id', $postSubcategoryId)->get();
            $postData->post__subcategories()->attach($postSubcategory);
        }
        return response([
            'data' => Post::where('id', $post->id)->with(['post__categories', 'post__subcategories'])->get()
        ], 201);
    }

    public function deletePost($id)
    {
        $post = Post::where('id', $id)->with(['post__categories', 'post__subcategories'])->get();
        if (count($post) === 0) {
            return response(['message' => '404 Not Found'], 404);
        }
        foreach ($post[0]->post__categories as $item) {
            $postmaintype = Post_Category::where('id', $item->id)->firstOrFail();
            $postmaintype->post_count = $postmaintype->post_count - 1;
            $postmaintype->save();
        }
        foreach ($post[0]->post__subcategories as $item) {
            $postsubtype = Post_Subcategory::where('id', $item->id)->firstOrFail();
            $postsubtype->post_count = $postsubtype->post_count - 1;
            $postsubtype->save();
        }
        if (Storage::exists('public' . $post[0]->image)) {
            $this->deleteImage($post[0]->image, 'post');
        }
        if (Storage::exists('public' . $post[0]->pdf)) {
            Storage::delete('public' . $post[0]->pdf);
        }
        Post::where('id', $id)->delete();
        return response()->json([
            'message' => 'Post has been deleted successfully.'
        ]);
    }

    public function updatePost(Request $request, $id)
    {
        $post_type = new Post_Subcategory;
        $post = Post::find($id);
        if (!$post) {
            return response(['message' => '404 Not Found'], 404);
        }
        if (empty($request->all())) {
            return response(['message' => 'No Data Change']);
        }
        $title_validator = Validator::make(
            $request->all(),
            [
                'title_kh' => 'nullable|unique:posts',
                'title_en' => 'nullable|unique:posts',
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'content_kh' => 'nullable|required_with:title_kh',
                'content_en' => 'nullable|required_with:title_en',
                'image' => 'nullable|mimes:jpg,jpeg,png,webp|file',
                'pdf' => 'nullable|mimes:pdf|file',
                'post.*.category' => 'sometimes|required|exists:post__categories,id',
                'post.*.subcategory' => 'sometimes|exists:post__subcategories,id',
                'post_modified' => 'date_format:Y-m-d H:i:s'
            ],
            [
                'post.*.category.exists' => 'Invalid Post Category Id',
                'post.*.subcategory.exists' => 'Invalid Post Subcategory Id'
            ]
        );
        if ($title_validator->fails()) {
            return response(['message' => $title_validator->errors()], 409);
        }
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $current = ($request->post_modified) ? $request->post_modified : Carbon::now();
        $request['modified'] = $current;
        $request['updated_at'] = $current;
        $data = $request->all();
        if ($request->content_kh) {
            $excerpt_kh = strip_tags($request->content_kh);
            $excerpt_kh = Str::words($excerpt_kh, 10);
            $data['excerpt_kh'] = $excerpt_kh;
        }
        if ($request->content_en) {
            $excerpt_en = strip_tags($request->content_en);
            $excerpt_en = Str::words($excerpt_en, 50);
            $data['excerpt_en'] = $excerpt_en;
        }
        $post = Post::where('id', $id)->firstOrFail();
        if ($request->image) {
            $post_img = $this->imageResizeAndSave($request, 'post', 'post');
            $data['image'] = $post_img;
            $this->deleteImage($post->image, 'post');
        }
        if ($request->pdf) {
            if (empty($request->pdf[0])) {
                $data['pdf'] = '';
                if ($post->pdf) {
                    Storage::delete('public' . $post->pdf);
                }
            } else {
                $pdf_name = $request->pdf->getClientOriginalName();
                $post_pdf = Storage::putFileAs('public/post', $request->pdf, $pdf_name);
                $data['pdf'] = trim($post_pdf, 'public');
                if ($post->pdf) {
                    Storage::delete('public' . $post->pdf);
                }
            }
        }
        if ($request->author) {
            $data['author'] = $request->author;
        }

        if ($request->title_en) {
            $baseSlugEn = Str::lower(str_replace(' ', '-', Str::limit($request->title_en, 30, $end = '')));
            $slug_en = $baseSlugEn;
            $postWithSlugEn = Post::where('slug_en', $slug_en)->get();
            $slugEnIdx = 1;
            while (count($postWithSlugEn) > 0) {
                $slug_en = $baseSlugEn . '-' . $slugEnIdx;
                $slugEnIdx++;
                $postWithSlugEn = Post::where('slug_en', $slug_en)->get();
            };
            $data['slug_en'] = $slug_en;
        }
        if ($request->title_kh) {
            $baseSlugKh = Str::lower(urlencode(str_replace(' ', '-', Str::limit($request->title_kh, 30, $end = ''))));
            $slug_kh = $baseSlugKh;
            $postWithSlugKh = Post::where('slug_kh', $slug_kh)->get();
            $slugKhIdx = 1;
            while (count($postWithSlugKh) > 0) {
                $slug_kh = $baseSlugKh . '-' . $slugKhIdx;
                $slugKhIdx++;
                $postWithSlugKh = Post::where('slug_kh', $slug_kh)->get();
            };
            $data['slug_kh'] = $slug_kh;
        }
        if ($request->post) {
            $postCategoryId = [];
            $postSubcategoryId = [];
            foreach ($request->post as $item) {
                $category_id = $item['category'];
                $subcategory_id = null;
                $postmaintype = Post_Category::where('id', $item['category'])->firstOrFail();
                $postmaintype->post_count = $postmaintype->post_count + 1;
                $postmaintype->save();
                if (isset($item['subcategory'])) {
                    $subcategory_id = $item['subcategory'];
                    $post_subcategory = Post_Subcategory::where('id', $item['subcategory'])->where('post__categories_id', $item['category'])->get();
                    if (count($post_subcategory) === 0) {
                        return response(['message' => "Post Category does not contain this subcategory."], 400);
                    } else {
                        $postsubtype = Post_Subcategory::where('id', $item['category'])->firstOrFail();
                        $postsubtype->post_count = $postsubtype->post_count + 1;
                        $postsubtype->save();
                    }
                }
                if (!isset($item['subcategory'])) {
                    $posttype = $post_type->newQuery();
                    $posttype->whereHas('post__categories', function ($query) use ($item) {
                        $query->where('post__categories.id', $item['category']);
                    });
                    if (count($posttype->get()) != 0) {
                        return response()->json([
                            "message" => (['post__subcategories_id' => ['The post subcategory id field is required.']])
                        ], 400);
                    }
                }
                $postCategoryId[] = $category_id;
                $postSubcategoryId[] = $subcategory_id;
            }
            $postData = Post::find($post->id);
            $postCategory = Post_Category::whereIn('id', $postCategoryId)->get();
            $postData->post__categories()->sync($postCategory);
            if (count($postSubcategoryId) !== 0) {
                $postSubcategory = Post_Subcategory::whereIn('id', $postSubcategoryId)->get();
                $postData->post__subcategories()->sync($postSubcategory);
            }
        }
        $post->fill($data);
        $post->save();
        return PostResource::collection($post->where('id', $id)->get())->response();
    }

    public function searchFilter(Request $request)
    {
        $post = new Post;
        $post_Subtype = new Post_Subcategory;
        $post_Type = new Post_Category;

        $postCheck = $post->newQuery();
        $postsubtype = $post_Subtype->newQuery()->where(function ($query) use ($request) {
            $query->where('slug_kh', Str::lower(urlencode($request->input('subtype'))));
            $query->orWhere('slug_en', $request->input('subtype'));
        });
        $posttype = $post_Type->newQuery()->where(function ($query) use ($request) {
            $query->where('slug_kh', Str::lower(urlencode($request->input('type'))));
            $query->orWhere('slug_en', $request->input('type'));
        });
        if ((count($posttype->get()) == 0 && $request->input('type')) || (count($postsubtype->get()) == 0 && $request->input('subtype'))) {
            return response([
                'message' => '404 Not Found'
            ], 404);
        }
        $terms = explode(' ', $request['search']);
        $postCheck->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                $query->orWhere('title_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('title_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('tags', 'LIKE', '%' . $term . '%');
            }
        });
        if ($request->input('type') != null) {
            $postCheck->whereHas('post__categories', function ($query) use ($request) {
                $query->where('post__categories.slug_kh', Str::lower(urlencode($request->input('type'))));
                $query->orWhere('post__categories.slug_en', $request->input('type'));
            });
        }
        if ($request->input('subtype') != null) {
            $postCheck->whereHas('post__subcategories', function ($query) use ($request) {
                $query->where('post__subcategories.slug_kh', Str::lower(urlencode($request->input('subtype'))));
                $query->orWhere('post__subcategories.slug_en', $request->input('subtype'));
            });
        }
        if ($request->input('except') != null) {
            $exceptCategory_kh = Str::lower(urlencode($request->input('except')));
            $exceptCategory_en = Str::lower($request->input('except'));

            $postCheck->where(function ($query) use ($exceptCategory_kh, $exceptCategory_en) {
                $query->whereDoesntHave('post__categories')
                    ->orWhereHas('post__categories', function ($query) use ($exceptCategory_kh, $exceptCategory_en) {
                        $query->where('slug_kh', '<>', $exceptCategory_kh)
                            ->where('slug_en', '<>', $exceptCategory_en);
                    });
            });
        } else {
            $postCheck->has('post__categories');
        }
        $postCheck = $postCheck->with(['post__categories', 'post__subcategories', 'users']);
        if (count($postCheck->get()) === 0) {
            return response([
                'message' => 'No Content Matched'
            ]);
        } else {
            if ($request->input('groupby') == 'true') {
                $postCheck = $postCheck->get()->groupBy('post__categories_id');
                foreach ($postCheck as $key => $value) {
                    $postCheck[$key] = $value->take(10);
                }
                return $postCheck;
            } else {
                return $postCheck;
            }
        }
    }

    public function getArticleAndVideo(Request $request)
    {
        $post = new Post;
        $post_Type = new Post_Subcategory;
        $post_Main_Type = new Post_Category;
        $video = new Video;

        $postCheck = $post->newQuery();
        $videoCheck = $video->whereHas('post__categories');

        $postmain = $post_Type->newQuery()->where(function ($query) use ($request) {
            $query->where('slug_kh', Str::lower(urlencode($request->input('subtype'))));
            $query->orWhere('slug_en', $request->input('subtype'));
        });
        $posttype = $post_Main_Type->newQuery()->where(function ($query) use ($request) {
            $query->where('slug_kh', Str::lower(urlencode($request->input('type'))));
            $query->orWhere('slug_en', $request->input('type'));
        });
        if ((count($posttype->get()) == 0 && $request->input('type')) || (count($postmain->get()) == 0 && $request->input('subtype'))) {
            return response([
                'message' => '404 Not Found'
            ], 404);
        }
        $terms = explode(' ', $request['search']);
        $postCheck->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                $query->orWhere('title_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('title_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('tags', 'LIKE', '%' . $term . '%');
            }
        });
        $videoCheck->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                return $query->orWhere('title_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('title_kh', 'LIKE', '%' . $term . '%');
            }
        });
        if ($request->input('type') != null) {
            $postCheck->whereHas('post__categories', function ($query) use ($request) {
                $query->where('post__categories.slug_kh', Str::lower(urlencode($request->input('type'))));
                $query->orWhere('post__categories.slug_en', $request->input('type'));
            });

            $videoCheck->whereHas('post__categories', function ($query) use ($request) {
                $query->where('post__categories.slug_kh', Str::lower(urlencode($request->input('type'))));
                $query->orWhere('post__categories.slug_en', $request->input('type'));
            });
        }
        if ($request->input('subtype') != null) {
            $postCheck->whereHas('post__subcategories', function ($query) use ($request) {
                $query->where('post__subcategories.slug_kh', Str::lower(urlencode($request->input('subtype'))));
                $query->orWhere('post__subcategories.slug_en', $request->input('subtype'));
            });

            $videoCheck->whereHas('post__subcategories', function ($query) use ($request) {
                $query->where('post__subcategories.slug_kh', Str::lower(urlencode($request->input('type'))));
                $query->orWhere('post__subcategories.slug_en', $request->input('type'));
            });
        }
        if ($request->input('except') !== null) {
            $exceptCategory_kh = Str::lower(urlencode($request->input('except')));
            $exceptCategory_en = Str::lower($request->input('except'));

            $postCheck->where(function ($query) use ($exceptCategory_kh, $exceptCategory_en) {
                $query->whereDoesntHave('post__categories')
                    ->orWhereHas('post__categories', function ($query) use ($exceptCategory_kh, $exceptCategory_en) {
                        $query->where('slug_kh', '<>', $exceptCategory_kh)
                            ->where('slug_en', '<>', $exceptCategory_en);
                    });
            });
        }
        if ($request->input('video_filter') !== null) {
            if ($request->input('video_filter') === 'all') {
                $videoCheck->where('id', '=', NULL);
            } else {
                $filteredCategory_kh = Str::lower(urlencode($request->input('video_filter')));
                $filteredCategory_en = Str::lower($request->input('video_filter'));

                $videoCheck->where(function ($query) use ($filteredCategory_en, $filteredCategory_kh) {
                    $query->whereDoesntHave('post__categories')
                        ->orWhereHas('post__categories', function ($query) use ($filteredCategory_en, $filteredCategory_kh) {
                            $query->where('slug_kh', '<>', $filteredCategory_kh)
                                ->where('slug_en', '<>', $filteredCategory_en);
                        });
                });
            }
        }
        $postCheck = $postCheck->with(['post__categories', 'post__subcategories', 'users']);
        $videoCheck = $videoCheck->with(['post__categories', 'post__subcategories']);
        if (count($postCheck->get()) === 0 and $videoCheck->get()->count() === 0) {
            return response([
                'message' => 'No Content Matched'
            ]);
        } else {
            if ($request->input('groupby') == 'true') {
                $postCheck = $postCheck->get()->groupBy('post__categories_id');
                $videoCheck = $videoCheck->get()->groupBy('post__categories_id');
                foreach ($postCheck as $key => $value) {
                    $postCheck[$key] = $value->take(10);
                }
                foreach ($videoCheck as $key => $value) {
                    $videoCheck[$key] = $value->take(10);
                }
                $postCheck->merge($videoCheck);
                return $postCheck;
            } else {
                $postCheck = PostResource::collection($postCheck->get());
                $videoCheck = VideoResource::collection($videoCheck->get());
                $paginationLimit = $request->input('limit', 15);
                $data = $postCheck->merge($videoCheck)->paginate($paginationLimit);
                $data = $data->toArray();
                $temp = [];
                foreach ($data['data'] as $item) {
                    $temp[] = $item;
                }
                $temp = collect($temp);
                $data['data'] = $temp->sortByDesc('date')->values()->all();
                return $data;
            }
        }
    }

    public function showPostBySlug($slug)
    {
        $post = new Post;
        $post = $post->newQuery()->where(function ($query) use ($slug) {
            $query->where('slug_kh', Str::lower(urlencode($slug)));
            $query->orWhere('slug_en', $slug);
        });
        $post = $post->with(['post__categories', 'post__subcategories', 'users'])->get();
        if (count($post) === 0) {
            return response([
                'message' => '404 Not Found'
            ], 404);
        }
        $post[0]->view = $post[0]->view + 1;
        $post[0]->save();
        // get previous post
        $previous = Post::where('id', '<', $post[0]->id);
        // get next post
        $next = Post::where('id', '>', $post[0]->id);
        if (count($previous->get()) !== 0) {
            $previous = $previous->orderBy('id', 'desc')->firstOrFail();
        } else {
            $previous = $previous->get();
        }
        if (count($next->get()) !== 0) {
            $next = $next->orderBy('id')->firstOrFail();
        } else {
            $next = $next->get();
        }
        return [
            'previous' => $previous,
            'data' => PostResource::collection($post),
            'next' => $next
        ];
    }

    public function searchFilterAllPost(Request $request, Post $post, MB_Learner $mb_learner, MB_Professional $mb_professional, Video $video)
    {
        $postCheck = $post->newQuery();
        $mbcheck = $mb_learner->newQuery();
        $mbprocheck = $mb_professional->newQuery();
        $videoCheck = $video->newQuery();
        $terms = explode(' ', $request['search']);
        $postCheck->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                return $query->orWhere('title_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('title_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('tags', 'LIKE', '%' . $term . '%');
            }
        });
        $mbcheck->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                return $query->orWhere('title_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('title_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('tags', 'LIKE', '%' . $term . '%');
            }
        });
        $mbprocheck->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                return $query->orWhere('title_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('title_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('tags', 'LIKE', '%' . $term . '%');
            }
        });
        $videoCheck->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                return $query->orWhere('title_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('title_en', 'LIKE', '%' . $term . '%');
            }
        });
        $postCheck = $postCheck->where('date', '<', now())->with(['post__categories', 'post__subcategories', 'users']);
        $mbcheck = $mbcheck->where('date', '<', now())->with(['m_b__categories', 'm_b__subcategories', 'users']);
        $mbprocheck = $mbcheck->where('date', '<', now())->with(['users']);
        $videoCheck = $videoCheck->where('date', '<', now())->with(['post__categories', 'post__subcategories', 'm_b__categories']);
        $data = [];
        $data['post'] = $postCheck->get()->take(10);
        $data['mb_learner'] = $mbcheck->get()->take(10);
        $data['mb_professional'] = $mbprocheck->get()->take(10);
        $data['video'] = $videoCheck->get()->take(10);
        return $data;
    }

    public function adminSearchFunction(Request $request)
    {
        $postCheck = PostController::searchFilter($request);
        if ($postCheck instanceof Response) {
            return $postCheck;
        }
        $postCheck->where('date', '<', now());
        $scheduledPostIds = $postCheck->where('status', 'Scheduled')->pluck('id')->toArray();
        if (!empty($scheduledPostIds)) {
            Post::whereIn('id', $scheduledPostIds)->update(['status' => 'Publish']);
        }

        $postCheck = PostController::searchFilter($request);
        $postCheck = PostResource::collection($postCheck->orderBy('date', 'desc')->paginate($request['limit']));
        if ($request->input('essential') === 'true') {
            return PostController::postEssentialData($postCheck);
        } else {
            return $postCheck;
        }
    }

    public function customerSearchFunction(Request $request)
    {
        $postCheck = PostController::searchFilter($request);
        if ($postCheck instanceof Response) {
            return $postCheck;
        }
        $postCheck->where('date', '<', now());
        $scheduledPostIds = $postCheck->where('status', 'Scheduled')->pluck('id')->toArray();
        if (!empty($scheduledPostIds)) {
            Post::whereIn('id', $scheduledPostIds)->update(['status' => 'Publish']);
        }

        $postCheck = PostController::searchFilter($request);
        $postCheck = PostResource::collection($postCheck->where('date', '<', now())->where('status', 'Publish')->orderBy('date', 'desc')->paginate($request['limit']));
        if ($request->input('essential') === 'true') {
            $postEssential = PostController::postEssentialData($postCheck);
            return $postEssential;
        } else {
            return $postCheck;
        }
    }

    public function adminGetArticle($slug)
    {
        $postData = PostController::showPostBySlug($slug);
        return response($postData);
    }

    public function customerGetArticle($slug)
    {
        $postData = PostController::showPostBySlug($slug);
        if ($postData['data'][0]['date'] < now() && $postData['data'][0]['status'] === 'Publish') {
            return response($postData);
        } else {
            return response(['message' => 'You are accessing forbidden content.'], 403);
        }
    }

    public function postEssentialData($postData)
    {
        $data = [];
        foreach ($postData as $post_item) {
            $category = [];
            $subcategory = [];
            $category[] = $post_item->post__categories->first();
            $subcategory[] = $post_item->post__subcategories->where('post__categories_id', $category[0]->id)->first();
            $data[] = [
                'id' => $post_item->id,
                'title_kh' => $post_item->title_kh,
                'title_en' => $post_item->title_en,
                'slug_kh' => $post_item->slug_kh,
                'slug_en' => $post_item->slug_en,
                'image' => $post_item->image,
                'author' => $post_item->author,
                'category' => $category,
                'subcategory' => $subcategory,
                'date' => $post_item->date,
                'status' => $post_item->status
            ];
        }
        $p['data'] = $data;
        $p['current_page'] = $postData->currentPage();
        $p['last_page'] = $postData->lastPage();
        $p['total'] = $postData->total();
        $p = collect($p);
        return $p;
    }
}

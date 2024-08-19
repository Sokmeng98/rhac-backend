<?php

namespace App\Http\Controllers;

use App\Models\MB_Category;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\MB_Learner;
use App\Models\MB_Professional;
use App\Models\Video;
use App\Http\Resources\MBResource;
use App\Http\Resources\VideoResource;
use App\Models\MB_Subcategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class MBLearnerController extends Controller
{
    public function createMB(Request $request)
    {
        $current = ($request->post_date) ? $request->post_date : Carbon::now();
        $title_validator = Validator::make(
            $request->all(),
            [
                'title_kh' => 'nullable|unique:m_b__learners',
                'title_en' => 'nullable|unique:m_b__learners',
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'image' => 'mimes:jpg,jpeg,png,webp|file',
                'pdf' => 'array',
                'pdf.*' => 'mimes:pdf|file',
                'm_b__categories' => 'required',
                'm_b__categories.*.m_b__categories_id' => 'required|exists:m_b__categories,id',
                'm_b__categories.*.m_b__subcategories_id' => 'sometimes|exists:m_b__subcategories,id',
                'post_date' => 'date_format:Y-m-d H:i:s'
            ],
            [
                'm_b__categories.required' => 'The mb category id is required',
                'm_b__categories.*.m_b__categories_id.exists' => 'Invalid method bank category id',
                'm_b__categories.*.m_b__categories_id.required' => 'Method Bank category id if required.',
                'm_b__categories.*.m_b__subcategories_id.exists' => 'Invalid method bank subcategory id',
                'pdf.*.mimes' => 'The pdf must a file of type: pdf'
            ]
        );
        if ($title_validator->fails()) {
            return response(['message' =>  $title_validator->errors()], 409);
        }
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        if ($request->title_kh == null && $request->title_en == null) {
            return response(['message' => "Either title khmer or english should not be empty!"], 400);
        }
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
        $postWithSlugEn = MB_Learner::where('slug_en', $slug_en)->get();
        $postWithSlugKh = MB_Learner::where('slug_kh', $slug_kh)->get();

        while (count($postWithSlugEn) > 0 || count($postWithSlugKh) > 0) {
            if (count($postWithSlugEn) > 0) {
                $slug_en = $baseSlugEn . '-' . $slugEnIdx;
                $slugEnIdx++;
                $postWithSlugEn = MB_Learner::where('slug_en', $slug_en)->get();
            }

            if (count($postWithSlugKh) > 0) {
                $slug_kh = $baseSlugKh . '-' . $slugKhIdx;
                $slugKhIdx++;
                $postWithSlugKh = MB_Learner::where('slug_kh', $slug_kh)->get();
            }
        }

        $mb_img = "";
        if ($request->image) {
            $mb_img = Storage::put('public/MB', $request->image);
        }
        $mb_pdf = [];
        if ($request->pdf) {
            foreach ($request->pdf as $file) {
                $pdf_name = $file->getClientOriginalName();
                $store = Storage::putFileAs('public/MB', $file, $pdf_name);
                array_push($mb_pdf, trim($store, 'public'));
            }
        }
        $status = $current > now() ? 'Scheduled' : 'Publish';
        $author = $request->author ?? 'RHAC';
        $mb = MB_Learner::create([
            'image' => ltrim($mb_img, 'public'),
            'title_kh' => $request->title_kh,
            'title_en' => $request->title_en,
            'content_kh' => $request->content_kh,
            'content_en' => $request->content_en,
            'excerpt_kh' => $excerpt_kh,
            'excerpt_en' => $excerpt_en,
            'pdf' => $mb_pdf,
            'tags' => $request->tags,
            'view' => 0,
            'author' => $author,
            'date' => $current,
            'users_id' => Auth::user()->id,
            'slug_en' => $slug_en,
            'slug_kh' => $slug_kh,
            'status' => $status,
        ]);
        $mbCategoryId = [];
        $mbSubcategoryId = [];
        $sameCategoryCheck = 0;
        foreach ($request->m_b__categories as $item) {
            $category_id = $item['m_b__categories_id'];
            $subcategory_id = null;
            $mbMainType = MB_Category::where('id', $category_id)->firstOrFail();
            if ($sameCategoryCheck !== $mbMainType->id) {
                $mbMainType->count = $mbMainType->count + 1;
                $mbMainType->save();
                $sameCategoryCheck = $mbMainType->id;
            }
            if (isset($item['m_b__subcategories_id'])) {
                $subcategory_id = $item['m_b__subcategories_id'];
                $mbSubType = MB_Subcategory::where('id', $subcategory_id)->where('m_b__categories_id', $category_id)->get();
                if (count($mbSubType) === 0) {
                    return response(['message' => 'Method Bank Category does not contain this subcategory.'], 400);
                } else {
                    $mbSubType = MB_Subcategory::where('id', $subcategory_id)->firstOrFail();
                    $mbSubType->count = $mbSubType->count + 1;
                    $mbSubType->save();
                }
            }
            if (!isset($item['m_b__subcategories_id'])) {
                $mb_type = new MB_Category();
                $mbtype = $mb_type->newQuery();
                $mbtype->whereHas('m_b__categories', function ($query) use ($item) {
                    $query->where('m_b__categories_id', $item['m_b__categories_id']);
                });
                if (count($mbtype->get()) !== 0) {
                    return response()->json([
                        'message' => (['m_b__subcategories_id' => ['The method bank subcategory id is required']])
                    ], 400);
                }
            }
            $mbCategoryId[] = $category_id;
            $mbSubcategoryId[] = $subcategory_id;
        }
        $mbData = MB_Learner::find($mb->id);
        $mbCategory = MB_Category::whereIn('id', $mbCategoryId)->get();
        $mbData->m_b__categories()->attach($mbCategory);
        if (count($mbSubcategoryId) !== 0) {
            $mbSubcategory = MB_Subcategory::whereIn('id', $mbSubcategoryId)->get();
            $mbData->m_b__subcategories()->attach($mbSubcategory);
        }
        return response([
            'data' => MB_Learner::where('id', $mb->id)->with(['m_b__categories', 'm_b__subcategories'])->get()
        ], 201);
    }

    public function deleteMB($id)
    {
        $mbLearner = MB_Learner::where('id', $id)->with(['m_b__categories'])->get();
        if (count($mbLearner) === 0) {
            return response(['message' => '404 Not Found.'], 404);
        }
        foreach ($mbLearner[0]->m_b__categories as $item) {
            $mbtype = MB_Category::where('id', $item->id)->firstOrFail();
            $mbtype->count = $mbtype->count - 1;
            $mbtype->save();
        }
        if (Storage::exists('public' . $mbLearner[0]->image)) {
            Storage::delete('public' . $mbLearner[0]->image);
        }
        if ($mbLearner[0]->pdf) {
            Storage::delete(array_map(fn ($pdf) => 'public' . $pdf, $mbLearner[0]->pdf));
        }
        MB_Learner::where('id', $id)->delete();
        return response()->json([
            'message' => 'Method Bank Learner has been deleted successfully'
        ]);
    }

    public function updateMB(Request $request, $id, MB_Category $mb_type)
    {
        if (!MB_Learner::find($id)) {
            return response(['message' => '404 Not Found'], 404);
        }
        if (empty($request->all())) {
            return response(['message' => 'No Data Change']);
        }
        $title_validator = Validator::make(
            $request->all(),
            [
                'title_kh' => 'nullable|unique:m_b__learners',
                'title_en' => 'nullable|unique:m_b__learners',
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'image' => 'nullable|mimes:jpg,jpeg,png,webp|file',
                'old_pdf' => 'array',
                'old_pdf.*' => 'ends_with:.pdf',
                'new_pdf' => 'array',
                'new_pdf.*' => 'mimes:pdf|file',
                'm_b__categories' => 'required',
                'm_b__categories.*.m_b__categories_id' => 'required|exists:m_b__categories,id',
                'm_b__categories.*.m_b__subcategories_id' => 'sometimes|exists:m_b__subcategories,id',
                'post_modified' => 'date_format:Y-m-d H:i:s'
            ],
            [
                'm_b__categories.required' => 'The mb category id is required',
                'm_b__categories.*.m_b__categories_id.exists' => 'Invalid method bank category id',
                'm_b__categories.*.m_b__categories_id.required' => 'Method Bank category id if required.',
                'm_b__categories.*.m_b__subcategories_id.exists' => 'Invalid method bank subcategory id',
                'old_pdf.*.ends_with' => 'The pdf must be a file of type: pdf',
                'new_pdf.*.mimes' => 'The pdf must be a file of type: pdf.'
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
        $mbCategoryId = $request->m_b__categories_id;
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
        $mb = MB_Learner::where('id', $id)->firstOrFail();
        if ($request->image) {
            $mb_img = Storage::put('public/MB', $request->image);
            $data['image'] = ltrim($mb_img, 'public');
        }
        $newPdf = [];
        if ($request->old_pdf) {
            if (empty($request->old_pdf[0])) {
                if (!empty($mb->pdf)) {
                    Storage::delete(array_map(fn ($pdf) => 'public' . $pdf, $mb->pdf));
                }
                $newPdf = [];
            } else {
                foreach ($request->old_pdf as $pdf) {
                    $newPdf[] = '/MB/' . $pdf;
                }
            }
            if (!empty($mb->pdf)) {
                $pdfToDelete = array_diff($mb->pdf, $newPdf);
                if (!empty($pdfToDelete)) {
                    Storage::delete(array_map(fn ($pdf) => 'public' . $pdf, $pdfToDelete));
                }
            }
        }
        if ($request->new_pdf) {
            foreach ($request->new_pdf as $pdf) {
                $pdfName = $pdf->getClientOriginalName();
                $newPdf[] = trim(Storage::putFileAs('public/MB', $pdf, $pdfName), 'public');
            }
        }
        if ($request->author) {
            $data['data'] = $request->author;
        }
        $data['pdf'] = (!$request->old_pdf && $request->new_pdf) ? array_merge($mb->pdf, $newPdf) : ((!$request->old_pdf && !$request->new_pdf) ? $mb->pdf : $newPdf);
        if ($request->title_en) {
            $baseSlugEn = Str::lower(str_replace(' ', '-', Str::limit($request->title_en, 30, $end = '')));
            $slug_en = $baseSlugEn;
            $postWithSlugEn = MB_Learner::where('slug_en', $slug_en)->get();
            $slugEnIdx = 1;
            while (count($postWithSlugEn) > 0) {
                $slug_en = $baseSlugEn . '-' . $slugEnIdx;
                $slugEnIdx++;
                $postWithSlugEn = MB_Learner::where('slug_en', $slug_en)->get();
            };
            $data['slug_en'] = $slug_en;
        }
        if ($request->title_kh) {
            $baseSlugKh = Str::lower(urlencode(str_replace(' ', '-', Str::limit($request->title_kh, 30, $end = ''))));
            $slug_kh = $baseSlugKh;
            $postWithSlugKh = MB_Learner::where('slug_kh', $slug_kh)->get();
            $slugKhIdx = 1;
            while (count($postWithSlugKh) > 0) {
                $slug_kh = $baseSlugKh . '-' . $slugKhIdx;
                $slugKhIdx++;
                $postWithSlugKh = MB_Learner::where('slug_kh', $slug_kh)->get();
            };
            $data['slug_kh'] = $slug_kh;
        }
        $mb->fill($data);
        $mb->save();
        $mbCategoryId = [];
        $mbSubcategoryId = [];
        $sameCategoryCheck = 0;
        if ($request->m_b__categories) {
            foreach ($request->m_b__categories as $item) {
                $category_id = $item['m_b__categories_id'];
                $subcategory_id = null;
                $mbMainType = MB_Category::where('id', $category_id)->firstOrFail();
                if ($sameCategoryCheck !== $mbMainType->id) {
                    $mbMainType->count = $mbMainType->count + 1;
                    $mbMainType->save();
                    $sameCategoryCheck = $mbMainType->id;
                }
                if (isset($item['m_b__subcategories_id'])) {
                    $subcategory_id = $item['m_b__subcategories_id'];
                    $mbSubType = MB_Subcategory::where('id', $subcategory_id)->where('m_b__categories_id', $category_id)->get();
                    if (count($mbSubType) === 0) {
                        return response(['message' => 'Method Bank Category does not contain this subcategory.'], 400);
                    } else {
                        $mbSubType = MB_Subcategory::where('id', $subcategory_id)->firstOrFail();
                        $mbSubType->count = $mbSubType->count + 1;
                        $mbSubType->save();
                    }
                }
                if (!isset($item['m_b__subcategories_id'])) {
                    $mbtype = $mb_type->newQuery();
                    $mbtype->whereHas('m_b__categories', function ($query) use ($item) {
                        $query->where('m_b__categories_id', $item['m_b__categories_id']);
                    });
                    if (count($mbtype->get()) !== 0) {
                        return response()->json([
                            'message' => (['m_b__subcategories_id' => ['The method bank subcategory id is required']])
                        ], 400);
                    }
                }
                $mbCategoryId[] = $category_id;
                $mbSubcategoryId[] = $subcategory_id;
            }
            $mbData = MB_Learner::find($mb->id);
            $mbCategory = MB_Category::whereIn('id', $mbCategoryId)->get();
            $mbData->m_b__categories()->sync($mbCategory);
            if (count($mbSubcategoryId) !== 0) {
                $mbSubcategory = MB_Subcategory::whereIn('id', $mbSubcategoryId)->get();
                $mbData->m_b__subcategories()->sync($mbSubcategory);
            }
        }

        return MBResource::collection($mb->where('id', $id)->with(['m_b__categories', 'm_b__subcategories'])->get())->response();
    }

    public function showMBBySlug($slug)
    {
        $mb_learner = new MB_Learner;
        $mb = $mb_learner->newQuery()->where(function ($query) use ($slug) {
            $query->where('slug_kh', Str::lower(urlencode($slug)));
            $query->orWhere('slug_en', $slug);
        });
        $mb = $mb->with(['m_b__categories', 'm_b__subcategories', 'users'])->get();
        if (count($mb) === 0) {
            return response(['message' => '404 Not Found'], 404);
        }
        $mb[0]->view = $mb[0]->view + 1;
        $mb[0]->save();
        // get previous post
        $previous = MB_Learner::where('id', '<', $mb[0]->id);
        // get next post
        $next = MB_Learner::where('id', '>', $mb[0]->id);
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
            'data' => MBResource::collection($mb),
            'next' => $next
        ];
    }

    public function searchFilter(Request $request)
    {
        $mb_learner = new MB_Learner;
        $mb_type = new MB_Category;
        $mb_sub_type = new MB_Subcategory;

        $mbcheck = $mb_learner->newQuery();
        $mbtype = $mb_type->newQuery()->where(function ($query) use ($request) {
            $query->where('slug_kh', Str::lower(urlencode($request->input('type'))));
            $query->orWhere('slug_en', $request->input('type'));
        });
        $mbsubtype = $mb_sub_type->newQuery()->where(function ($query) use ($request) {
            $query->where('slug_kh', Str::lower(urlencode($request->input('subtype'))));
            $query->orWhere('slug_en', $request->input('subtype'));
        });

        if ((count($mbtype->get()) === 0 && $request->input('type')) || (count($mbsubtype->get()) === 0 && $request->input('subtype'))) {
            return response(['message' => '404 Not Found'], 404);
        }
        $terms = explode(' ', $request['search']);
        $mbcheck->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                return $query->orWhere('title_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('title_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('tags', 'LIKE', '%' . $term . '%');
            }
        });
        if ($request->input('type') != null) {
            $mbcheck->whereHas('m_b__categories', function ($query) use ($request) {
                $query->where('m_b__categories.slug_kh', Str::lower(urlencode($request->input('type'))));
                $query->orWhere('m_b__categories.slug_en', $request->input('type'));
            });
        }
        if ($request->input('subtype') != null) {
            $mbcheck->whereHas('m_b__subcategories', function ($query) use ($request) {
                $query->where('m_b__subcategories.slug_kh', Str::lower(urlencode($request->input('subtype'))));
                $query->orWhere('m_b__subcategories.slug_en', $request->input('subtype'));
            });
        }
        $mbcheck = $mbcheck->with(['m_b__categories', 'm_b__subcategories', 'users']);
        if ($mbcheck->get()->count() === 0) {
            return response(['message' => 'No Content Matched']);
        } else {
            if ($request->input('groupby') == 'true') {
                $mbcheck = $mbcheck->get()->groupBy('m_b__categories_id');
                foreach ($mbcheck as $key => $value) {
                    $mbcheck[$key] = $value->take(10);
                }
                return $mbcheck;
            } else {
                return $mbcheck;
            }
        }
    }

    public function getArticleAndVideo(Request $request, MB_Learner $mb_learner, MB_Category $mb_type, Video $video)
    {
        $mbCheck = $mb_learner->newQuery();
        $videoCheck = $video->whereHas('m_b__categories');

        $mbtype = $mb_type->newQuery()->where(function ($query) use ($request) {
            $query->where('slug_kh', Str::lower(urlencode($request->input('type'))));
            $query->orWhere('slug_en', $request->input('type'));
        });
        if ((count($mbtype->get()) === 0 && $request->input('type'))) {
            return response([
                'message' => '404 Not Found'
            ], 404);
        };
        $terms = explode(' ', $request['search']);
        $mbCheck->where(function ($query) use ($terms) {
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
        if ($request->input('type') !== null) {
            $mbCheck->whereHas('m_b__categories', function ($query) use ($request) {
                $query->where('m_b__categories.slug_kh', Str::lower(urlencode($request->input('type'))));
                $query->orWhere('m_b__categories.slug_en', $request->input('type'));
            });
            $videoCheck->whereHas('m_b__categories', function ($query) use ($request) {
                $query->where('m_b__categories.slug_kh', Str::lower(urlencode($request->input('type'))));
                $query->orWhere('m_b__categories.slug_en', $request->input('type'));
            });
        }
        if ($request->input('subtype') !== null) {
            $mbCheck->whereHas('m_b__subcategories', function ($query) use ($request) {
                $query->where('m_b__subcategories.slug_kh', Str::lower(urlencode($request->input('subtype'))));
                $query->orWhere('m_b__subcategories.slug_en', $request->input('subtype'));
            });
            $videoCheck->whereHas('m_b__subcategories', function ($query) use ($request) {
                $query->where('m_b__subcategories.slug_kh', Str::lower(urlencode($request->input('subtype'))));
                $query->orWhere('m_b__subcategories.slug_en', $request->input('subtype'));
            });
        }
        $mbCheck = $mbCheck->with(['m_b__categories', 'm_b__subcategories', 'users']);
        $videoCheck = $videoCheck->with(['m_b__categories']);
        if ($mbCheck->get()->count() === 0 and $videoCheck->get()->count() === 0) {
            return response(['message' => 'No Content Matched']);
        } else {
            if ($request->input('groupby') == 'true') {
                $mbCheck = $mbCheck->get()->groupBy('m_b__categories_id');
                $videoCheck = $videoCheck->get()->groupBy('m_b__categories_id');
                foreach ($mbCheck as $key => $value) {
                    $mbCheck[$key] = $value->take(10);
                }
                foreach ($videoCheck as $key => $value) {
                    $videoCheck[$key] = $value->take(10);
                }
                $mbCheck->merge($videoCheck)->groupBy('m_b__categories_id');
                return $mbCheck;
            } else {
                $paginationLimit = ($request['limit'] === null or $request['limit'] === 0) ? 15 : $request['limit'];
                $mbCheck = MBResource::collection($mbCheck->orderBy('id', 'desc')->get());
                $videoCheck = VideoResource::collection($videoCheck->orderBy('id', 'desc')->get());
                $data = $mbCheck->merge($videoCheck)->paginate($paginationLimit);
                $data = $data->toArray();
                $temp = [];
                foreach ($data['data'] as $item) {
                    $temp[] = $item;
                }
                $data['data'] = $temp;
                return $data;
            }
        }
    }

    public function searchFilterAllMBArticles(Request $request, MB_Professional $mb_professional, MB_Learner $mb_learner, Video $video)
    {
        $mbCheck = $mb_learner->newQuery();
        $mbproCheck = $mb_professional->newQuery();
        $videoCheck = $video->newQuery();

        $terms = explode(' ', $request['search']);
        $mbCheck->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                return $query->orWhere('title_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('title_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('content_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('tags', 'LIKE', '%' . $term . '%');
            }
        });
        $mbproCheck->where(function ($query) use ($terms) {
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
        $mbCheck = $mbCheck->with(['m_b__categories', 'm_b__subcategories', 'users']);
        $videoCheck = $videoCheck->with(['m_b__categories', 'm_b__subcategories']);

        $paginationLimit = $request->input('limit', 15);
        if ($mbCheck->get()->count() === 0 and $videoCheck->get()->count() === 0 and $mbproCheck->get()->count() === 0) {
            return response(['message' => 'No Content Matched']);
        } else {
            $mbCheck = MBResource::collection($mbCheck->get());
            $mbproCheck = MBResource::collection($mbproCheck->get());
            if ($request->has('maintype')) {
                $maintype = $request['maintype'];
                switch ($maintype) {
                    case 'mb_professional':
                        $videoCheck = VideoResource::collection($videoCheck->where('mb_professional', '!=', '[]')->get());
                        $data = $mbproCheck->merge($videoCheck)->paginate($paginationLimit);
                        break;
                    case 'mb_learner':
                        $videoCheck = VideoResource::collection($videoCheck->whereHas('m_b__categories')->get());
                        $data = $mbCheck->merge($videoCheck)->paginate($paginationLimit);
                        break;
                    default:
                        return response(['message' => 'Provided input is invalid. MainType is either mb_professional or mb_learner'], 400);
                }
            } else {
                $videoCheck = VideoResource::collection($videoCheck->whereHas('m_b__categories')->orWhere('mb_professional', '!=', '[]')->get());
                $data = $mbCheck->merge($videoCheck)->merge($mbproCheck)->paginate($paginationLimit);
            }
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

    public function customerSearchFunction(Request $request)
    {
        $mblearner = MBLearnerController::searchFilter($request);
        if ($mblearner instanceof Response) {
            return $mblearner;
        }
        $mblearner->where('date', '<', now());
        $scheduledArticleIds = $mblearner->where('status', 'Scheduled')->pluck('id')->toArray();
        if (!empty($scheduledArticleIds)) {
            MB_Learner::whereIn('id', $scheduledArticleIds)->update(['status' => 'Publish']);
        }

        $mblearner = MBLearnerController::searchFilter($request);
        $mblearner = MBResource::collection($mblearner->where('date', '<', now())->where('status', 'Publish')->orderBy('date', 'desc')->paginate($request['limit']));
        return $mblearner;
    }

    public function adminSearchFunction(Request $request)
    {
        $mblearner = MBLearnerController::searchFilter($request);
        if ($mblearner instanceof Response) {
            return $mblearner;
        }
        $mblearner->where('date', '<', now());
        $scheduledArticleIds = $mblearner->where('status', 'Scheduled')->pluck('id')->toArray();
        if (!empty($scheduledArticleIds)) {
            MB_Learner::whereIn('id', $scheduledArticleIds)->update(['status' => 'Publish']);
        }

        $mblearner = MBLearnerController::searchFilter($request);
        return MBResource::collection($mblearner->orderBy('date', 'desc')->paginate($request['limit']));
    }

    public function customerGetArticle($slug)
    {
        $mblearner = MBLearnerController::showMBBySlug($slug);
        if ($mblearner['data'][0]['date'] < now() && $mblearner['data'][0]['status'] === 'Publish') {
            return $mblearner;
        } else {
            return response(['message' => 'You are accessing the forbidden content'], 403);
        }
    }

    public function adminGetArticle($slug)
    {
        $mblearner = MBLearnerController::showMBBySlug($slug);
        return response($mblearner);
    }
}

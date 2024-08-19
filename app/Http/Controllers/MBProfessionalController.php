<?php

namespace App\Http\Controllers;

use App\Http\Resources\MBResource;
use App\Models\MB_Professional;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MBProfessionalController extends Controller
{
    public function createMBProfessional(Request $request)
    {
        $current = ($request->post_date) ? $request->post_date : Carbon::now();
        $title_validator = Validator::make(
            $request->all(),
            [
                'title_kh' => 'nullable|unique:m_b__professionals',
                'title_en' => 'nullable|unique:m_b__professionals',
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'image' => 'nullable|mimes:jpg,jpeg,png,webp|file',
                'pdf' => 'nullable|array',
                'pdf.*' => 'mimes:pdf|file',
                'grade.*' => 'required|in:Grade 5&6,Grade 7-9,Grade 10-12',
                'post_date' => 'date_format:Y-m-d H:i:s'
            ],
            [
                'pdf.*.mimes' => 'Pdf must be a file of type: pdf'
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
        $postWithSlugEn = MB_Professional::where('slug_en', $slug_en)->get();
        $postWithSlugKh = MB_Professional::where('slug_kh', $slug_kh)->get();

        while (count($postWithSlugEn) > 0 || count($postWithSlugKh) > 0) {
            if (count($postWithSlugEn) > 0) {
                $slug_en = $baseSlugEn . '-' . $slugEnIdx;
                $slugEnIdx++;
                $postWithSlugEn = MB_Professional::where('slug_en', $slug_en)->get();
            }

            if (count($postWithSlugKh) > 0) {
                $slug_kh = $baseSlugKh . '-' . $slugKhIdx;
                $slugKhIdx++;
                $postWithSlugKh = MB_Professional::where('slug_kh', $slug_kh)->get();
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
        $author = $request->author ?? 'RHAC';
        $status = $current > now() ? 'Scheduled' : 'Publish';
        $mb = MB_Professional::create([
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
            'grade' => $request->grade,
            'slug_en' => $slug_en,
            'slug_kh' => $slug_kh,
            'status' => $status,
        ]);
        return response([
            'data' => $mb
        ], 201);
    }

    public function deleteMBProfessional($id)
    {
        $mbProfessional = MB_Professional::find($id);
        if (!$mbProfessional) {
            return response(['message' => '404 Not Found.'], 404);
        }
        MB_Professional::where('id', $id)->delete();
        if (Storage::exists('public' . $mbProfessional->image)) {
            Storage::delete('public' . $mbProfessional->image);
        }
        if ($mbProfessional->pdf) {
            Storage::delete(array_map(fn ($pdf) => 'public' . $pdf, $mbProfessional->pdf));
        }
        return response()->json([
            'message' => 'Method bank professional has been deleted successfully.'
        ]);
    }

    public function updateMBProfessional(Request $request, $id)
    {
        if (!MB_Professional::find($id)) {
            return response(['message' => '404 Not Found'], 404);
        }
        if (empty($request->all())) {
            return response(['message' => 'No Data Change']);
        }
        $title_validator = Validator::make(
            $request->all(),
            [
                'title_kh' => 'nullable|unique:m_b__professionals',
                'title_en' => 'nullable|unique:m_b__professionals',
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'image' => 'nullable|mimes:jpg,jpeg,png,webp|file',
                'old_pdf' => 'nullable|array',
                'old_pdf.*' => 'ends_with:.pdf',
                'new_pdf' => 'nullable|array',
                'new_pdf.*' => 'mimes:pdf|file',
                'grade.*' => 'sometimes|required|in:Grade 5&6,Grade 7-9,Grade 10-12',
                'post_modified' => 'date_format:Y-m-d H:i:s'
            ],
            [
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
        $mb = MB_Professional::where('id', $id)->firstOrFail();
        if ($request->image) {
            $mb_img = Storage::put('public/MB', $request->image);
            $data['image'] = ltrim($mb_img, 'public');

            Storage::delete('public' . $mb->image);
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
            $data['author'] = $request->author;
        }
        $data['pdf'] = (!$request->old_pdf && $request->new_pdf) ? array_merge($mb->pdf, $newPdf) : ((!$request->old_pdf && !$request->new_pdf) ? $mb->pdf : $newPdf);
        if ($request->title_en) {
            $baseSlugEn = Str::lower(str_replace(' ', '-', Str::limit($request->title_en, 30, $end = '')));
            $slug_en = $baseSlugEn;
            $postWithSlugEn = MB_Professional::where('slug_en', $slug_en)->get();
            $slugEnIdx = 1;
            while (count($postWithSlugEn) > 0) {
                $slug_en = $baseSlugEn . '-' . $slugEnIdx;
                $slugEnIdx++;
                $postWithSlugEn = MB_Professional::where('slug_en', $slug_en)->get();
            };
            $data['slug_en'] = $slug_en;
        }
        if ($request->title_kh) {
            $baseSlugKh = Str::lower(urlencode(str_replace(' ', '-', Str::limit($request->title_kh, 30, $end = ''))));
            $slug_kh = $baseSlugKh;
            $postWithSlugKh = MB_Professional::where('slug_kh', $slug_kh)->get();
            $slugKhIdx = 1;
            while (count($postWithSlugKh) > 0) {
                $slug_kh = $baseSlugKh . '-' . $slugKhIdx;
                $slugKhIdx++;
                $postWithSlugKh = MB_Professional::where('slug_kh', $slug_kh)->get();
            };
            $data['slug_kh'] = $slug_kh;
        }
        $mb->fill($data);
        $mb->save();
        return MBResource::collection($mb->where('id', $id)->get())->response();
    }

    public function showMBBySlug($slug)
    {
        $mb_professional = new MB_Professional;
        $mb = $mb_professional->newQuery()->where(function ($query) use ($slug) {
            $query->where('slug_kh', Str::lower(urlencode($slug)));
            $query->orWhere('slug_en', $slug);
        });
        $mb = $mb->with(['users'])->get();
        if (count($mb) === 0) {
            return response(['message' => '404 Not Found'], 404);
        }
        $mb[0]->view = $mb[0]->view + 1;
        $mb[0]->save();
        // get previous post
        $previous = MB_Professional::where('id', '<', $mb[0]->id);
        // get next post
        $next = MB_Professional::where('id', '>', $mb[0]->id);
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
        $mb_professional = new MB_Professional;
        $mbcheck = $mb_professional->newQuery();
        if ($request->has('type')) {
            $type = $request->input('type') === 'Grade 5' ? 'Grade 5&6' : $request->input('type');
            $mbtype = $mbcheck->newQuery()->when($request->has('type'), function ($query) use ($type) {
                $query->orWhereJsonContains('grade', $type);
            });
            if ($mbtype->count() === 0) {
                return response([
                    'message' => 'No Article of Type \'' . $type . '\''
                ]);
            }
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
        $mbcheck = $mbcheck->with(['users']);
        if (count($mbcheck->get()) === 0) {
            return response(['message' => 'No Content Matched']);
        } else {
            if ($request->input('groupby') == 'true') {
                $mbcheck = $mbcheck->get()->groupBy('grade');
                foreach ($mbcheck as $key => $value) {
                    $mbcheck[$key] = $value->take(10);
                }
                return $mbcheck;
            } else {
                return $mbcheck;
            }
        }
    }

    public function getArticleAndVideo(Request $request, MB_Professional $mb_professional, Video $video)
    {
        $mbcheck = $mb_professional->newQuery();
        $videoCheck = $video->where('mb_professional', '!=', '[]');
        if ($request->has('type')) {
            $type = $request->input('type') === 'Grade 5' ? 'Grade 5&6' : $request->input('type');
            $mbcheck = $mbcheck->newQuery()->when($request->has('type'), function ($query) use ($type) {
                $query->orWhereJsonContains('grade', $type);
            });
            if ($mbcheck->count() === 0) {
                return response([
                    'message' => 'No Article of Category: \'' . $type . '\''
                ]);
            }
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
        $videoCheck->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                return $query->orWhere('title_en', 'LIKE', '%' . $term . '%')
                    ->orWhere('title_kh', 'LIKE', '%' . $term . '%');
            }
        });
        $mbcheck = $mbcheck->with(['users']);
        if (count($mbcheck->get()) === 0 and $videoCheck->get()->count() === 0) {
            return response(['message' => 'No Content Matched']);
        } else {
            if ($request->input('groupby') == 'true') {
                $mbcheck = $mbcheck->get()->groupBy('grade');
                foreach ($mbcheck as $key => $value) {
                    $mbcheck[$key] = $value->take(10);
                }
                foreach ($videoCheck as $key => $value) {
                    $videoCheck[$key] = $value->take(10);
                }
                $mbcheck->array_merge($videoCheck);
                return $mbcheck;
            } else {
                $mbCheck = MBResource::collection($mbcheck->get());
                $videoCheck = VideoResource::collection($videoCheck->get());
                $paginationLimit = $request->input('limit', 15);
                $data = $mbCheck->merge($videoCheck)->paginate($paginationLimit);
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

    public function customerSearchFunction(Request $request)
    {
        $mbprofessional = MBProfessionalController::searchFilter($request);
        if ($mbprofessional instanceof Response) {
            return $mbprofessional;
        }
        $mbprofessional->where('date', '<', now());

        $scheduledArticleIds = $mbprofessional->where('status', 'Scheduled')->pluck('id')->toArray();
        if (!empty($scheduledArticleIds)) {
            MB_Professional::whereIn('id', $scheduledArticleIds)->update(['status' => 'Publish']);
        }

        $mbprofessional = MBProfessionalController::searchFilter($request);
        return MBResource::collection($mbprofessional->where('date', '<', now())->where('status', 'Publish')->orderBy('date', 'desc')->paginate($request['limit']));
    }

    public function adminSearchFunction(Request $request)
    {
        $mbprofessional = MBProfessionalController::searchFilter($request);
        if ($mbprofessional instanceof Response) {
            return $mbprofessional;
        }
        $mbprofessional->where('date', '<', now());

        $scheduledArticleIds = $mbprofessional->where('status', 'Scheduled')->pluck('id')->toArray();
        if (!empty($scheduledArticleIds)) {
            MB_Professional::whereIn('id', $scheduledArticleIds)->update(['status' => 'Publish']);
        }

        $mbprofessional = MBProfessionalController::searchFilter($request);
        return MBResource::collection($mbprofessional->orderBy('date', 'desc')->paginate($request['limit']));
    }

    public function customerGetArticle($slug)
    {
        $mbprofessional = MBProfessionalController::showMBBySlug($slug);
        if ($mbprofessional['data'][0]['date'] < now() && $mbprofessional['data'][0]['status'] === 'Publish') {
            return $mbprofessional;
        } else {
            return response(['message' => 'You are accessing the forbidden content'], 403);
        }
    }

    public function adminGetArticle($slug)
    {
        $mbprofessional = MBProfessionalController::showMBBySlug($slug);
        return response($mbprofessional);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use Illuminate\Http\Request;
use App\Http\Resources\PublicationResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PublicationController extends Controller
{
    public function createPublication(Request $request)
    {
        $current = ($request->post_date) ? $request->post_date : Carbon::now();
        $title_validator = Validator::make(
            $request->all(),
            [
                'title_kh' => 'unique:publications',
                'title_en' => 'unique:publications'
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'image' => 'nullable|mimes:jpg,jpeg,png,webp|file',
                'pdf' => 'required|mimes:pdf|file',
                'post_date' => 'date_format:Y-m-d H:i:s'
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
        $image = null;
        if ($request->image) {
            $image = Storage::put('public/publication', $request->image);
        }
        $pdf_name = $request->pdf->getClientOriginalName();
        $pdf = Storage::putFileAs('public/publication', $request->pdf, $pdf_name);

        $data = Publication::create([
            'title_kh' => $request->title_kh,
            'title_en' => $request->title_en,
            'image' => ltrim($image, 'public'),
            'pdf' => trim($pdf, 'public'),
            'date' => $current,
        ]);
        return response(['data' => $data], 201);
    }

    public function deletePublication($id)
    {
        $data = Publication::find($id);
        if (!$data) {
            return response(['message' => '404 Not Found'], 404);
        }
        Publication::where('id', $id)->delete();
        if (Storage::exists('public' . $data->pdf)) {
            Storage::delete('public' . $data->pdf);
        }
        if (Storage::exists('public' . $data->image)) {
            Storage::delete('public' . $data->image);
        }
        return response()->json([
            'message' => 'Publication has been deleted successfully.'
        ]);
    }

    public function showPublication(Publication $pub, Request $request)
    {
        if (empty($request['limit']) || is_numeric($request['limit'])) {
            return PublicationResource::collection($pub->orderBy('id', 'desc')->paginate($request['limit']));
        } else {
            return response(['message' => 'Provided data is invalid'], 400);
        }
    }

    public function updatePublication(Request $request, $id)
    {
        $pub = Publication::find($id);
        if (!$pub) {
            return response(['message' => '404 Not Found'], 404);
        }
        $title_validator = Validator::make(
            $request->all(),
            [
                'title_kh' => 'unique:publications',
                'title_en' => 'unique:publications',
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'image' => 'nullable|mimes:jpg,jpeg,png,webp|file',
                'pdf' => 'nullable|mimes:pdf|file',
                'post_modified' => 'date_format:Y-m-d H:i:s'
            ]
        );
        $current = ($request->post_modified) ? $request->post_modified : Carbon::now();
        $request['modified'] = $current;
        if ($title_validator->fails()) {
            return response(['message' => $title_validator->errors()], 409);
        }
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $data = $request->all();
        if ($request->image) {
            if (Storage::exists('public' . $pub->image)) {
                Storage::delete('public' . $pub->image);
            }
            $image = Storage::put('public/publication', $request->image);
            $data['image'] = ltrim($image, 'public');
        }
        if ($request->pdf) {
            if (Storage::exists('public' . $pub->pdf)) {
                Storage::delete('public' . $pub->pdf);
            }
            $pdf_name = $request->pdf->getClientOriginalName();
            $pdf = Storage::putFileAs('public/publication', $request->pdf, $pdf_name);
            $data['pdf'] = trim($pdf, 'public');
        }
        $pub->fill($data);
        $pub->save();
        return PublicationResource::collection($pub->where('id', $id)->get())->response();
    }

    public function showPublicationById($id)
    {
        $data = Publication::where('id', $id)->get();
        if (count($data) === 0) {
            return response(['message' => '404 Not Found'], 404);
        }
        return PublicationResource::collection($data)->response();
    }

    public function searchFilter(Publication $pub, Request $request)
    {
        $pubCheck = $pub->newQuery();
        $terms = explode(' ', $request['search']);
        $pubCheck->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                return $query->orWhere('title_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('title_en', 'LIKE', '%' . $term . '%');
            }
        });

        if (count($pubCheck->get()) === 0) {
            return response(['message' => 'No Content Matched']);
        } else {
            return PublicationResource::collection($pubCheck->orderBy('id', 'desc')->paginate($request['limit']));
        }
    }
}

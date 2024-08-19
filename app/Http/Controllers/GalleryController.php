<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use App\Http\Resources\GalleryResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GalleryController extends Controller
{
    public function createGallery(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'image' => 'required|mimes:jpg,jpeg,png,webp|file',
            ]
        );
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $gallery = Storage::put('public/gallery', $request->image);
        $gallery1 = Gallery::create([
            'image' => ltrim($gallery, 'public'),
        ]);
        return response(['data' => $gallery1], 201);
    }

    public function deleteGallery($id)
    {
        $gallery = Gallery::find($id);
        if (!$gallery) {
            return response(['message' => '404 Not Found'], 404);
        }
        Gallery::where('id', $id)->delete();
        if (Storage::exists('public' . $gallery->image)) {
            Storage::delete('public' . $gallery->image);
        }
        return response()->json([
            'message' => 'Gallery has been deleted successfully.'
        ]);
    }

    public function showGallery(Gallery $gallery, Request $request)
    {
        if (empty($request['limit']) || is_numeric($request['limit'])) {
            return GalleryResource::collection($gallery->orderBy('id', 'desc')->paginate($request['limit']));
        } else {
            return response(['message' => 'Provided data is invalid'], 400);
        }
    }

    public function updateGallery(Request $request, $id)
    {
        if (!Gallery::find($id)) {
            return response(['message' => '404 Not Found'], 404);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'image' => 'sometimes|required|mimes:jpg,jpeg,png,webp|file'
            ]
        );
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $data = $request->all();
        $gallery = Gallery::where('id', $id)->firstOrFail();
        $image = Storage::put('public/gallery', $request->image);
        if (Storage::exists('public' . $gallery->image)) {
            Storage::delete('public' . $gallery->image);
        }
        $data['image'] = ltrim($image, 'public');
        $gallery->fill($data);
        $gallery->save();
        return GalleryResource::collection($gallery->where('id', $id)->get())->response();
    }

    public function showGalleryById(Request $request, $id)
    {
        $data = Gallery::where('id', $id)->get();
        if (count($data) === 0) {
            return response(['message' => '404 Not Found'], 404);
        }
        return GalleryResource::collection($data)->response();
    }
}

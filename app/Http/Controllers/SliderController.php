<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slider;
use App\Http\Resources\SliderResource;
use App\Traits\ImageTrait;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    use ImageTrait;
    public function createSlider(Request $request)
    {
        $request->validate([
            'img' => 'required',
        ]);
        $slider = $this->imageResizeAndSave($request, 'images');
        $slider1 = Slider::create([
            'img' => $slider,
        ]);
        return response([
            'status' => 201,
            'data' => $slider1
        ], 201);
    }

    public function deleteSlider($id)
    {
        $old_data = Slider::where('id', $id)->get();
        if (count($old_data) == 0) {
            return response(['message' => '404 Not Found'], 404);
        }
        Slider::where('id', $id)->delete();
        if (Storage::exists('public' . $old_data[0]->img)) {
            $this->deleteImage($old_data[0]->img, 'images');
        }
        return response()->json([
            'status' => 200,
            'message' => 'You have been delete successfully'
        ]);
    }

    public function showSlider(Slider $slider, Request $request)
    {
        return SliderResource::collection($slider->paginate($request['limit']));
    }

    public function updateSlider(Request $request, $id)
    {
        $data = $request->all();
        $slider = Slider::where('id', $id)->firstOrFail();
        $img = $this->imageResizeAndSave($request, 'images');
        if (Storage::exists('public' . $slider->img)) {
            $this->deleteImage($slider->img, 'images');
        }
        $data['img'] = trim($img, 'public');
        $slider->fill($data);
        $slider->save();
        return SliderResource::collection($slider->where('id', $id)->get())->response();
    }

    public function showSliderById(Request $request, $id)
    {
        $data = Slider::where('id', $id)->get();
        return SliderResource::collection($data)->response();
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\What_we_do;
use App\Http\Resources\WhatwedoResource;

class WhatwedoController extends Controller
{
    public function createWhatwedo(Request $request)
    {
        $request->validate([
            'title_kh' => 'unique:what_we_dos',
            'title_en' => 'unique:what_we_dos',
        ]);
        $whatwedo = What_we_do::create([
            'icon' => $request->icon,
            'title_kh' => $request->title_kh,
            'title_en' => $request->title_en,
            'subtitle_kh' => $request->subtitle_kh,
            'subtitle_en' => $request->subtitle_en,
        ]);
        return response([
            'status' => 201,
            'data' => $whatwedo
        ], 201);
    }

    public function deleteWhatwedo($id)
    {
        What_we_do::where('id', $id)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'You have been delete successfully'
        ]);
    }

    public function showWhatwedo(What_we_do $whatwedo, Request $request)
    {
        return WhatwedoResource::collection($whatwedo->paginate($request['limit']));
    }

    public function updateWhatwedo(Request $request, $id)
    {
        $data = $request->all();
        $whatwedo = What_we_do::where('id', $id)->firstOrFail();
        $whatwedo->fill($data);
        $whatwedo->save();
        return WhatwedoResource::collection($whatwedo->where('id', $id)->get())->response();
    }

    public function showWhatwedoById(Request $request, $id)
    {
        $whatwedo = What_we_do::where('id', $id)->get();
        return WhatwedoResource::collection($whatwedo)->response();
    }
}

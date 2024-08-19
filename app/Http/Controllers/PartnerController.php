<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partner;
use App\Http\Resources\PartnerResource;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    public function createPartner(Request $request)
    {
        $request->validate([
            'img' => 'required',
        ]);
        $partner = Storage::put('public/partner', $request->img);
        $partner1 = Partner::create([
            'img' => ltrim($partner, 'public'),
        ]);
        return response([
            'status' => 201,
            'data' => $partner1
        ], 201);
    }

    public function deletePartner($id)
    {
        $old_data = Partner::where('id', $id)->get();
        Partner::where('id', $id)->delete();
        if (Storage::exists('public' . $old_data[0]->img)) {
            Storage::delete('public' . $old_data[0]->img);
        }
        return response()->json([
            'status' => 200,
            'message' => 'You have been delete successfully'
        ]);
    }

    public function showPartner(Partner $partner, Request $request)
    {
        return PartnerResource::collection($partner->paginate($request['limit']));
    }

    public function updatePartner(Request $request, $id)
    {
        $data = $request->all();
        $partner = Partner::where('id', $id)->firstOrFail();
        $img = Storage::put('public/partner', $request->img);
        if (Storage::exists('public' . $partner->img)) {
            Storage::delete('public' . $partner->img);
        }
        $data['img'] = ltrim($img, 'public');
        $partner->fill($data);
        $partner->save();
        return PartnerResource::collection($partner->where('id', $id)->get())->response();
    }

    public function showPartnerById(Request $request, $id)
    {
        $data = Partner::where('id', $id)->get();
        return PartnerResource::collection($data)->response();
    }
}

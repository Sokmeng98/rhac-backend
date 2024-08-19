<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Http\Resources\ServiceResource;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function createService(Request $request)
    {
        $service = "";
        if ($request->img) {
            $service = Storage::put('public/service', $request->img);
        }
        $service = Service::create([
            'name_en' => $request->name_en,
            'name_kh' => $request->name_kh,
            'img' => trim($service, 'public'),
        ]);
        return response([
            'status' => 201,
            'data' => $service
        ], 201);
    }

    public function deleteService($id)
    {
        $old_data = Service::where('id', $id)->get();
        Service::where('id', $id)->delete();
        if (Storage::exists('public' . $old_data[0]->img)) {
            Storage::delete('public' . $old_data[0]->img);
        }
        return response()->json([
            'status' => 200,
            'message' => 'You have been delete successfully'
        ]);
    }

    public function showService(Service $service, Request $request)
    {
        return ServiceResource::collection($service->paginate($request['limit']));
    }

    public function updateService(Request $request, $id)
    {
        $data = $request->all();
        $service = Service::where('id', $id)->firstOrFail();
        if ($request->img) {
            $img = Storage::put('public/service', $request->img);
            $data['img'] = trim($img, 'public');
        }
        if (Storage::exists('public' . $service->img)) {
            Storage::delete('public' . $service->img);
        }
        $service->fill($data);
        $service->save();
        return ServiceResource::collection($service->where('id', $id)->get())->response();
    }

    public function showServiceById(Request $request, $id)
    {
        $data = Service::where('id', $id)->get();
        return ServiceResource::collection($data)->response();
    }
}

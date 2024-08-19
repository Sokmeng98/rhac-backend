<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Http\Resources\BranchResource;

class BranchController extends Controller
{
    public function createBranch(Request $request) {
        $request->validate([
            'title' => 'required|unique:branches',
        ]);
        $url_coordinates_position = strpos($request->url, '@')+1;
        $coordinates = [];
        if ($url_coordinates_position != false) {
            $coordinates_string = substr($request->url, $url_coordinates_position);
            $coordinates_array = explode(',', $coordinates_string);

            if (count($coordinates_array) >= 2) {
                $longitude = $coordinates_array[0];
                $latitude = $coordinates_array[1];

                $coordinates = [
                    "longitude" => $longitude,
                    "latitude" => $latitude
                ];
            }
        }
        $branch = Branch::create([
            'title' => $request->title,
            'address' => $request->address,
            'phone' => $request->phone,
            'coordinate' => $request->coordinate?$request->coordinate:$coordinates,
            'content' => $request->content,
            'url' => $request->url
        ]);
        return response([
            'status' => 201,
            'data' => $branch
        ], 201);
    }

    public function deleteBranch($id){
        Branch::where('id', $id)->delete();
        return response()->json([
           'status' => 200,
           'message' => 'You have been delete successfully'
        ]);
    }

    public function showBranch(Branch $branch, Request $request){
        return BranchResource::collection($branch->paginate($request['limit']));
    }

    public function updateBranch(Request $request, $id){
        $url_coordinates_position = strpos($request->url, '@')+1;
        $coordinates = [];
        if ($url_coordinates_position != false) {
            $coordinates_string = substr($request->url, $url_coordinates_position);
            $coordinates_array = explode(',', $coordinates_string);

            if (count($coordinates_array) >= 2) {
                $longitude = $coordinates_array[0];
                $latitude = $coordinates_array[1];

                $coordinates = [
                    "longitude" => $longitude,
                    "latitude" => $latitude
                ];
            }
        }
        $data = $request->all();
        $data['coordinate'] = $request->coordinate?$request->coordinate:$coordinates;
        $branch = Branch::where('id', $id)->firstOrFail();
        $branch->fill($data);
        $branch->save();
        return BranchResource::collection($branch->where('id',$id)->get())->response();
    }

    public function showBranchById(Request $request, $id){
        $data = Branch::where('id',$id)->get();
        return BranchResource::collection($data)->response();    
    }
}

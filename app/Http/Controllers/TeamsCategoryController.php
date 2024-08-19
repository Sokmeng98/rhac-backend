<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teams_Category;
use App\Http\Resources\TeamsCategoryResource;

class TeamsCategoryController extends Controller
{
    public function createTeamsCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:teams__categories',
        ]);
        $teams_category = Teams_Category::create([
            'name' => $request->name,
        ]);
        return response([
            'status' => 201,
            'data' => $teams_category
        ], 201);
    }

    public function deleteTeamsCategory($id)
    {
        Teams_Category::where('id', $id)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'You have been delete successfully'
        ]);
    }

    public function showTeamsCategory(Teams_Category $teams_category, Request $request)
    {
        return TeamsCategoryResource::collection($teams_category->paginate($request['limit']));
    }

    public function updateTeamsCategory(Request $request, $id)
    {
        $data = $request->all();
        $teams_category = Teams_Category::where('id', $id)->firstOrFail();
        $teams_category->fill($data);
        $teams_category->save();
        return TeamsCategoryResource::collection($teams_category->where('id', $id)->get())->response();
    }

    public function showTeamsCategoryById(Request $request, $id)
    {
        $data = Teams_Category::where('id', $id)->get();
        return TeamsCategoryResource::collection($data)->response();
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teams;
use App\Http\Resources\TeamsResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class TeamsController extends Controller
{
    public function createTeams(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name_en' => 'required',
                'role_en' => 'required',
                'img' => 'sometimes|mimes:jpg,jpeg,png,webp|file',
                'type' => 'required|in:National Council,Staff',
                'order' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if ($value !== 'First Order' && (!is_numeric($value) && !is_int($value))) {
                            $fail($attribute . ' must be an integer if it is not \'First Order\'.');
                        }
                    },
                ]
            ]
        );
        if ($validator->fails()) {
            return response([
                'message' => $validator->errors()
            ], 400);
        }
        $img = "";
        if ($request->img) {
            $img_name = $request->img->getClientOriginalName();
            $img = Storage::putFileAs('public/teams', $request->img, $img_name);
        }
        if ($request->order) {
            $reorder = ($request->order  === 'First Order') ? 1 : $request->order;
            $increment = 2;
            $allTeamMembers = Teams::where('type', $request->type)->where('order', '>', $reorder)->orderBy('order', 'asc')->get();
            if ($request->order === 'First Order') {
                $oth_member = Teams::where('type', $request->type)->where('order', 1)->get();
                try {
                    $oth_member[0]->order = 2;
                    $oth_member[0]->save();
                } catch (Exception $e) {
                }
            }
            foreach ($allTeamMembers as $member) {
                $member->order = $reorder + $increment;
                $member->save();
                $increment = $increment + 1;
            }
            $request['order'] = ($request->order  === 'First Order') ? 1 : $request->order + 1;
        }
        $teams = Teams::create([
            'name_en' => $request->name_en,
            'name_kh' => $request->name_kh,
            'img' => ltrim($img, 'public'),
            'role_en' => $request->role_en,
            'role_kh' => $request->role_kh,
            'type' => $request->type,
            'order' => $request->order
        ]);
        return response([
            'data' => $teams
        ], 201);
    }

    public function deleteTeams($id)
    {
        $old_data = Teams::find($id);
        if (!$old_data) {
            return response(['message' => '404 Not Found'], 404);
        }
        $allTeamMembers = Teams::where('type', $old_data->type)->where('order', '>', $old_data->order)->where('id', '!=', $id)->orderBy('order', 'asc')->get();
        foreach ($allTeamMembers as $member) {
            $member->order -= 1;
            $member->save();
        }
        Teams::where('id', $id)->delete();
        if (Storage::exists('public' . $old_data->img)) {
            Storage::delete('public' . $old_data->img);
        }
        return response()->json([
            'message' => 'Team has been deleted successfully'
        ]);
    }

    public function showTeams(Teams $teams)
    {
        return TeamsResource::collection($teams->orderBy('order')->orderBy('updated_at', 'DESC')->get());
    }

    public function updateTeams(Request $request, $id)
    {
        if (!Teams::find($id)) {
            return response(['message' => '404 Not Found'], 404);
        }
        if (empty($request->all())) {
            return response(['message' => 'No Data Change'], 200);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'img' => 'sometimes|mimes:jpg,jpeg,png,webp|file',
                'name_en' => 'sometimes|required',
                'role_en' => 'sometimes|required',
                'type' => 'sometimes|required|in:National Council,Staff',
                'order' => [
                    'sometimes',
                    'required',
                    function ($attribute, $value, $fail) {
                        if ($value !== 'First Order' && (!is_numeric($value) && !is_int($value))) {
                            $fail($attribute . ' must be an integer if it is not \'First Order\'.');
                        }
                    },
                ]
            ]
        );
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $teams = Teams::where('id', $id)->firstOrFail();
        if ($request->order) {
            $reorder = ($request->order  === 'First Order') ? 1 : $request->order;
            $allTeamMembers = Teams::where('type', $teams->type)->where('order', '>', $reorder)->where('id', '!=', $id)->orderBy('order', 'asc')->get();
            $increment = 2;
            if ($request->order === 'First Order') {
                $oth_member = Teams::where('type', $teams->type)->where('order', 1)->first();
                try {
                    if ($oth_member->id != $id) {
                        $oth_member->order = 2;
                        $oth_member->save();
                    }
                } catch (Exception $e) {
                }
            }
            foreach ($allTeamMembers as $member) {
                $member->order = $reorder + $increment;
                $member->save();
                $increment = $increment + 1;
            }
            $request['order'] = ($request->order  === 'First Order') ? 1 : $request->order + 1;
        }
        $data = $request->all();
        if ($request->img) {
            if (Storage::exists('public' . $teams->img)) {
                Storage::delete('public' . $teams->img);
            }
            $img_name = $request->img->getClientOriginalName();
            $img = Storage::putFileAs('public/teams', $request->img, $img_name);
            $data['img'] = ltrim($img, 'public');
        }
        $teams->fill($data);
        $teams->save();
        return TeamsResource::collection($teams->where('id', $id)->get())->response();
    }

    public function getTeamsByType(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'type' => 'in:National Council,Staff'
            ]
        );
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $data = Teams::where('type', $request->type)->orderBy('order')->orderBy('updated_at', 'DESC')->get();
        return TeamsResource::collection($data)->response();
    }

    public function showTeamsById($id)
    {
        $data = Teams::where('id', $id)->get();
        if (count($data) === 0) {
            return response(['message' => '404 Not Found'], 404);
        }
        return TeamsResource::collection($data)->response();
    }

    public function searchFilter(Request $request, Teams $teams)
    {
        $teams = $teams->newQuery();
        $teams->where(function ($query) use ($request) {
            return $query->orWhere('name_en', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('name_kh', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('role_en', 'LIKE', '%' . $request->input('search') . '%')
                ->orWhere('role_kh', 'LIKE', '%' . $request->input('search') . '%');
        })->where('type', 'LIKE', '%' . $request->input('type') . '%');
        if (count($teams->get()) === 0) {
            return response([
                'message' => 'No Content Matched'
            ], 200);
        }
        return TeamsResource::collection($teams->orderBy('order')->orderBy('updated_at', 'DESC')->get());
    }
}

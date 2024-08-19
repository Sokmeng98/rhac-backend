<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MB_Professional_Learning;
use App\Http\Resources\MBProfessionalLearningResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MBProfessionalLearningController extends Controller
{
    public function creatembLearning(Request $request)
    {
        $title_validator = Validator::make(
            $request->all(),
            [
                'title_kh' => 'unique:m_b__professional__learnings',
                'title_en' => 'unique:m_b__professional__learnings'
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'type' => 'in:Glossary of CSE,List of IEC materials on CSE,List of additional resources for teachers,Comprehensive Sexuality Education (CSE)',
                'image' => 'sometimes|required|mimes:jpg,jpeg,png,webp|file',
                'pdf' => 'required|mimes:pdf|file'
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
            $image = Storage::put('public/mbProfessional_learning', $request->image);
        }
        $pdf_name = $request->pdf->getClientOriginalName();
        $pdf = Storage::putFileAs('public/mbProfessional_learning', $request->pdf, $pdf_name);
        $data = MB_Professional_Learning::create(
            [
                'title_kh' => $request->title_kh,
                'title_en' => $request->title_en,
                'type' => $request->type,
                'image' => ltrim($image, 'public'),
                'pdf' => trim($pdf, 'public')
            ]
        );
        return $data;
    }

    public function updatembLearning(Request $request, $id)
    {
        $mbLearning = MB_Professional_Learning::find($id);
        if (!$mbLearning) {
            return response(['message' => '404 Not Found'], 404);
        }
        $title_validator = Validator::make(
            $request->all(),
            [
                'title_kh' => 'unique:m_b__professional__learnings',
                'title_en' => 'unique:m_b__professional__learnings'
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'type' => 'in:Glossary of CSE,List of IEC materials on CSE,List of additional resources for teachers,Comprehensive Sexuality Education (CSE)',
                'image' => 'sometimes|required|mimes:jpg,jpeg,png,webp|file',
                'pdf' => 'sometimes|required|mimes:pdf|file'
            ]
        );
        if ($title_validator->fails()) {
            return response(['message' => $title_validator->errors()], 409);
        }
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $data = $request->all();
        if ($request->image) {
            if (Storage::exists('public' . $mbLearning->image)) {
                Storage::delete('public' . $mbLearning->image);
            }
            $image = Storage::put('public/mbProfessional_learning', $request->image);
            $data['image'] = ltrim($image, 'public');
        }
        if ($request->pdf) {
            if (Storage::exists('public' . $mbLearning->pdf)) {
                Storage::delete('public' . $mbLearning->pdf);
            }
            $pdf_name = $request->pdf->getClientOriginalName();
            $pdf = Storage::putFileAs('public/mbProfessional_learning', $request->pdf, $pdf_name);
            $data['pdf'] = trim($pdf, 'public');
        }
        $mbLearning->fill($data);
        $mbLearning->save();
        return MBProfessionalLearningResource::collection($mbLearning->where('id', $id)->get())->response();
    }

    public function deletembLearning($id)
    {
        $mbLearning = MB_Professional_Learning::find($id);
        if (!$mbLearning) {
            return response(['message' => '404 Not Found'], 404);
        }
        if (Storage::exists('public' . $mbLearning->image)) {
            Storage::delete('public' . $mbLearning->image);
        }
        if (Storage::exists('public' . $mbLearning->pdf)) {
            Storage::delete('public' . $mbLearning->pdf);
        }
        MB_Professional_Learning::where('id', $id)->delete();
        return response(['message' => 'Method Bank Professional Learning has been deleted sucessfully.']);
    }

    public function getmbLearning(Request $request, MB_Professional_Learning $mbLearning)
    {
        if (empty($request['limit']) || is_numeric($request['limit'])) {
            return MBProfessionalLearningResource::collection($mbLearning->orderBy('id', 'desc')->paginate($request['limit']));
        } else {
            return response(['message' => 'Provided data is invalid'], 400);
        }
    }

    public function getmbLearningById($id)
    {
        $data = MB_Professional_Learning::where('id', $id)->get();
        if (count($data) === 0) {
            return response(['message' => '404 Not Found'], 404);
        }
        return MBProfessionalLearningResource::collection($data)->response();
    }

    public function searchFilterForMbLearning(MB_Professional_Learning $mbLearning, Request $request)
    {
        $mbLearningCheck = $mbLearning->newQuery();
        $terms = explode(' ', $request['search']);
        $mbLearningCheck->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                return $query->orWhere('title_kh', 'LIKE', '%' . $term . '%')
                    ->orWhere('title_en', 'LIKE', '%' . $term . '%');
            }
        });
        if ($request->has('type')) {
            $mbLearningCheck->where('type', $request['type']);
        }
        if (count($mbLearning->get()) === 0) {
            return response(['message' => 'No Content Matched']);
        } else {
            return MBProfessionalLearningResource::collection($mbLearningCheck->orderBy('id', 'desc')->paginate($request['limit']));
        }
    }
}

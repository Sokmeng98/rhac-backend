<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FAQ;
use App\Http\Resources\FAQResource;
use Illuminate\Support\Facades\Validator;

class FAQController extends Controller
{
    public function createFAQ(Request $request)
    {
        $unique_validator = Validator::make(
            $request->all(),
            [
                'question_en' => 'unique:f_a_q_s|nullable',
                'question_kh' => 'unique:f_a_q_s|nullable'
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'question_en' => 'required',
                'answer_en' => 'required',
                'question_kh' => 'sometimes|required_with:answer_kh',
                'answer_kh' => 'sometimes|required_with:question_kh',
                'type' => 'required|in:Clinic Service,Health Information,Methodbank'
            ]
        );
        if ($unique_validator->fails()) {
            return response(['message' => $unique_validator->errors()], 409);
        }
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $faq = FAQ::create([
            'question_kh' => $request->question_kh,
            'question_en' => $request->question_en,
            'answer_kh' => $request->answer_kh,
            'answer_en' => $request->answer_en,
            'type' => $request->type
        ]);
        return response(['data' => $faq], 201);
    }

    public function deleteFAQ($id)
    {
        if (!FAQ::find($id)) {
            return response(['message' => '404 Not Found'], 404);
        }
        FAQ::where('id', $id)->delete();
        return response()->json(['message' => 'FAQ has been deleted successfully']);
    }

    public function updateFAQ(Request $request, $id)
    {
        if (!FAQ::find($id)) {
            return response(['message' => '404 Not Found'], 404);
        }
        $unique_validator = Validator::make(
            $request->all(),
            [
                'question_en' => 'unique:f_a_q_s|nullable',
                'question_kh' => 'unique:f_a_q_s|nullable'
            ]
        );
        $validator = Validator::make(
            $request->all(),
            [
                'question_en' => 'sometimes|required',
                'answer_en' => 'sometimes|required',
                'question_kh' => 'sometimes|required_with:answer_kh',
                'answer_kh' => 'sometimes|required_with:question_kh',
                'type' => 'sometimes|required|in:Clinic Service,Health Information,Methodbank'
            ]
        );
        if ($unique_validator->fails()) {
            return response(['message' => $unique_validator->errors()], 409);
        }
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $data = $request->all();
        $faq = FAQ::where('id', $id)->firstOrFail();
        $faq->fill($data);
        $faq->save();
        return FAQResource::collection($faq->where('id', $id)->get())->response();
    }

    public function showFAQById($id)
    {
        if (!FAQ::find($id)) {
            return response(['message' => '404 Not Found'], 404);
        }
        return FAQResource::collection(FAQ::where('id', $id)->get())->response();
    }

    public function getFAQByType(Request $request)
    {
        $type = $request->input('type');
        switch ($type) {
            case 'Clinic Service':
                break;
            case 'Health Information':
                break;
            case 'Methodbank':
                break;
            default:
                return response(['message' => 'Provided data is invalid'], 400);
        }
        $data = FAQ::where('type', $type)->get();
        return FAQResource::collection($data)->response();
    }

    public function searchFilter(Request $request, FAQ $faq)
    {
        $faqCheck = $faq->newQuery();
        if ($request->search) {
            $faqCheck->where(function ($query) use ($request) {
                return $query->orWhere('question_en', 'LIKE', '%' . $request->input('search') . '%')
                    ->orWhere('question_kh', 'LIKE', '%' . $request->input('search') . '%')
                    ->orWhere('answer_kh', 'LIKE', '%' . $request->input('search') . '%')
                    ->orWhere('answer_en', 'LIKE', '%' . $request->input('search') . '%')
                    ->orWhere('type', 'LIKE', '%' . $request->input('search') . '%');
            });
        }
        if ($request->type) {
            $type = $request->input('type');
            switch ($type) {
                case 'Clinic Service':
                    break;
                case 'Health Information':
                    break;
                case 'Methodbank':
                    break;
                default:
                    return response(['message' => 'Provided data is invalid'], 400);
            }
            $faqCheck->where('type', $type);
        }
        if (count($faqCheck->get()) === 0) {
            return response(['message' => 'No Content Matched']);
        } else {
            if ($request->input('groupby') == 'true') {
                $faqCheck = $faqCheck->get()->groupBy('type');
                foreach ($faqCheck as $key => $value) {
                    $faqCheck[$key] = $value->take(10);
                }
                return $faqCheck;
            } else {
                if (empty($request->limit) || is_numeric($request->limit)) {
                    return FAQResource::collection($faqCheck->orderBy('id', 'desc')->paginate($request['limit']))->response();
                } else {
                    return response(['message' => 'Provided data is invalid'], 400);
                }
            }
        }
    }
}

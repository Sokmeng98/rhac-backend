<?php

namespace App\Http\Controllers;

use App\Models\MB_Pdf;
use App\Http\Resources\MBPdfResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MBPdfController extends Controller
{
    public function createMBPdf(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'pdf' => 'required|mimes:pdf|file'
            ]
        );
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $pdf_name = $request->pdf->getClientOriginalName();
        $mb_pdf = Storage::putFileAs('public/MB', $request->pdf, $pdf_name);
        $mb_pdf1 = MB_Pdf::create([
            'pdf' => trim($mb_pdf, 'public'),
        ]);
        return response([
            'data' => $mb_pdf1
        ], 201);
    }

    public function deleteMBPdf($id)
    {
        $mbPdf = MB_Pdf::find($id);
        if (!$mbPdf) {
            return response(['message' => '404 Not Found'], 404);
        }
        MB_Pdf::where('id', $id)->delete();
        if (Storage::exists('public' . $mbPdf->pdf)) {
            Storage::delete('public' . $mbPdf->pdf);
        }
        return response()->json([
            'message' => 'MB Pdf has been deleted successfully.'
        ]);
    }

    public function showMBPdf(MB_Pdf $mb_pdf, Request $request)
    {
        if (is_numeric($request['limit']) || empty($request['limit'])) {
            $mbPdf = MBPdfResource::collection($mb_pdf->orderBy('id', 'desc')->paginate($request['limit']));
            if ((int)$request['page'] > $mbPdf->lastPage()) {
                return response(['message' => 'Page index out of range']);
            }
            return $mbPdf;
        } else {
            return response(['message' => 'Provided data is invlid.'], 400);
        }
    }

    public function updateMBPdf(Request $request, $id)
    {
        if (!MB_Pdf::find($id)) {
            return response(['message' => '404 Not Found'], 404);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'pdf' => 'sometimes|required|mimes:pdf|file'
            ]
        );
        if ($validator->fails()) {
            return response(['message' => $validator->errors()], 400);
        }
        $data = $request->all();
        $mb_pdf = MB_Pdf::where('id', $id)->firstOrFail();
        $pdf_name = $request->pdf->getClientOriginalName();
        $pdf = Storage::putFileAs('public/MB', $request->pdf, $pdf_name);
        if (Storage::exists('public' . $mb_pdf->pdf)) {
            Storage::delete('public' . $mb_pdf->pdf);
        }
        $data['pdf'] = trim($pdf, 'public');
        $mb_pdf->fill($data);
        $mb_pdf->save();
        return MBPdfResource::collection($mb_pdf->where('id', $id)->get())->response();
    }

    public function showMBPdfById($id)
    {
        $data = MB_Pdf::where('id', $id)->get();
        if (count($data) === 0) {
            return response(['message' => '404 Not Found'], 404);
        }
        return MBPdfResource::collection($data)->response();
    }

    public function searchFilter(Request $request, MB_Pdf $mb_pdf)
    {
        $mbcheck = $mb_pdf->newQuery();
        $mbcheck->where(function ($query) use ($request) {
            return $query->orWhere('pdf', 'LIKE', '%' . $request->input('search') . '%');
        });
        if ($mbcheck->get()->count() === 0) {
            return response(['message' => 'No Content Matched']);
        } else {
            return MBPdfResource::collection($mbcheck->orderBy('id', 'desc')->paginate($request['limit']));
        }
    }
}

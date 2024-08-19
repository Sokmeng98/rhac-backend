<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact_Us;
use App\Http\Resources\ContactUsResource;
use Mail;

class ContactUsController extends Controller
{
    public function createContactUs(Request $request)
    {
        $request->validate([
            'email' => 'required:contact__us',
        ]);
        $contact_us = Contact_Us::create([
            'email' => $request->email,
            'phone' => $request->phone,
            'name' => $request->name,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);
        $data = array('name' => $request->name, "body" => $request->message, "email" => $request->email);
        Mail::send('mail', $data, function ($message) use ($request) {
            $message->to('sangsonyrath17@kit.edu.kh', 'RHAC')->subject($request->subject);
            $message->from($request->email, $request->name);
        });

        return response([
            'status' => 201,
            'data' => $contact_us
        ], 201);
    }

    public function deleteContactUs($id)
    {
        Contact_Us::where('id', $id)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'You have been delete successfully'
        ]);
    }

    public function showContactUs(Contact_Us $contact_us, Request $request)
    {
        return ContactUsResource::collection($contact_us->paginate($request['limit']));
    }

    public function showContactUsById(Request $request, $id)
    {
        $data = Contact_Us::where('id', $id)->get();
        return ContactUsResource::collection($data)->response();
    }
}
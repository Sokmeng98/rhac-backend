<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscriber;
use App\Http\Resources\SubscriberResource;

class SubscriberController extends Controller
{
    public function createSubscriber(Request $request)
    {
        $request->validate([
            'email' => 'required|unique:subscribers',
            'name' => 'required',
        ]);
        $subscriber = Subscriber::create([
            'email' => $request->email,
            'name' => $request->name,
        ]);
        return response([
            'status' => 201,
            'data' => $subscriber
        ], 201);
    }

    public function deleteSubscriber($id)
    {
        Subscriber::where('id', $id)->delete();
        return response()->json([
            'status' => 200,
            'message' => 'You have been delete successfully'
        ]);
    }

    public function showSubscriber(Subscriber $subscriber, Request $request)
    {
        return SubscriberResource::collection($subscriber->paginate($request['limit']));
    }

    public function showSubscriberById(Request $request, $id)
    {
        $data = Subscriber::where('id', $id)->get();
        return SubscriberResource::collection($data)->response();
    }
}

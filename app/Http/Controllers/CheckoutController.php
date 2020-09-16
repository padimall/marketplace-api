<?php

namespace App\Http\Controllers;
use App\Checkout;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index()
    {
        $data = Checkout::all();
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],404);
        }
        return response()->json($data,200);
    }

    public function show($id)
    {
        $data = Checkout::find($id);
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],404);
        }
        return response()->json($data,200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $response = Checkout::create($data);
        return response()->json($response,201);
    }

    public function update(Request $request, Checkout $data)
    {
        $data->update($request->all());
        return response()->json($data,200);
    }

    public function delete($id){
        $data = Checkout::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

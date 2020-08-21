<?php

namespace App\Http\Controllers;

use App\Checkout_logistic;
use Illuminate\Http\Request;

class CheckoutLogisticController extends Controller
{
    public function index()
    {
        $data = Checkout_logistic::all();
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],404);
        }
        return response()->json($data,200);
    }

    public function show($id)
    {
        $data = Checkout_logistic::find($id);
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
        $response = Checkout_logistic::create($data);
        return response()->json($response,201);
    }

    public function update(Request $request, Checkout_logistic $data)
    {
        $data->update($request->all());
        return response()->json($data,200);
    }

    public function delete($id){
        $data = Checkout_logistic::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

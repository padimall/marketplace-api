<?php

namespace App\Http\Controllers;

use App\Checkout_payment;
use Illuminate\Http\Request;

class CheckoutPaymentController extends Controller
{
    public function index()
    {
        $data = Checkout_payment::all();
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],200);
        }
        return response()->json($data,200);
    }

    public function show($id)
    {
        $data = Checkout_payment::find($id);
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],200);
        }
        return response()->json($data,200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $response = Checkout_payment::create($data);
        return response()->json($response,201);
    }

    public function update(Request $request, Checkout_payment $data)
    {
        $data->update($request->all());
        return response()->json($data,200);
    }

    public function delete($id){
        $data = Checkout_payment::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

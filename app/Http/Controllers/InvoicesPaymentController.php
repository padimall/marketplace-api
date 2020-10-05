<?php

namespace App\Http\Controllers;

use App\Invoices_payment;
use Illuminate\Http\Request;

class InvoicesPaymentController extends Controller
{
    public function index()
    {
        $data = Invoices_payment::all();
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],200);
        }
        return response()->json($data,200);
    }

    public function show($id)
    {
        $data = Invoices_payment::find($id);
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
        $response = Invoices_payment::create($data);
        return response()->json($response,201);
    }

    public function update(Request $request, Invoices_payment $data)
    {
        $data->update($request->all());
        return response()->json($data,200);
    }

    public function delete($id){
        $data = Invoices_payment::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

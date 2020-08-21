<?php

namespace App\Http\Controllers;

use App\Invoices_logistic;
use Illuminate\Http\Request;

class InvoicesLogisticController extends Controller
{
    public function index()
    {
        $data = Invoices_logistic::all();
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],404);
        }
        return response()->json($data,200);
    }

    public function show($id)
    {
        $data = Invoices_logistic::find($id);
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
        $response = Invoices_logistic::create($data);
        return response()->json($response,201);
    }

    public function update(Request $request, Invoices_logistic $data)
    {
        $data->update($request->all());
        return response()->json($data,200);
    }

    public function delete($id){
        $data = Invoices_logistic::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

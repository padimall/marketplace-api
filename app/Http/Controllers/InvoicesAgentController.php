<?php

namespace App\Http\Controllers;

use App\Invoices_agent;
use Illuminate\Http\Request;

class InvoicesAgentController extends Controller
{
    public function index()
    {
        $data = Invoices_agent::all();
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],204);
        }
        return response()->json($data,200);
    }

    public function show($id)
    {
        $data = Invoices_agent::find($id);
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],204);
        }
        return response()->json($data,200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $response = Invoices_agent::create($data);
        return response()->json($response,201);
    }

    public function update(Request $request, Invoices_agent $data)
    {
        $data->update($request->all());
        return response()->json($data,200);
    }

    public function delete($id){
        $data = Invoices_agent::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

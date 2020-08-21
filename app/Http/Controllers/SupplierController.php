<?php

namespace App\Http\Controllers;

use App\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $data = Supplier::all();
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],404);
        }
        return response()->json($data,200);
    }

    public function show($id)
    {
        $data = Supplier::find($id);
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
        $response = Supplier::create($data);
        return response()->json($response,201);
    }

    public function update(Request $request, Supplier $data)
    {
        $data->update($request->all());
        return response()->json($data,200);
    }

    public function delete($id){
        $data = Supplier::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

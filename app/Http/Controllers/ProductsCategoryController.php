<?php

namespace App\Http\Controllers;

use App\Products_category;
use Illuminate\Http\Request;

class ProductsCategoryController extends Controller
{
    public function index()
    {
        $data = Products_category::all();
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],404);
        }
        return response()->json($data,200);
    }

    public function show($id)
    {
        $data = Products_category::find($id);
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
        $response = Products_category::create($data);
        return response()->json($response,201);
    }

    public function update(Request $request, Products_category $data)
    {
        $data->update($request->all());
        return response()->json($data,200);
    }

    public function delete($id){
        $data = Products_category::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

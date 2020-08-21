<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $data = Product::all();
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],404);
        }
        return response()->json($data,200);
    }

    public function show($id)
    {
        $data = Product::find($id);
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
        $response = Product::create($data);
        return response()->json($response,201);
    }

    public function update(Request $request, Product $data)
    {
        $data->update($request->all());
        return response()->json($data,200);
    }

    public function delete($id){
        $data = Product::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

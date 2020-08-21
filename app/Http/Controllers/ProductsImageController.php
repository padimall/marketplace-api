<?php

namespace App\Http\Controllers;

use App\Products_image;
use Illuminate\Http\Request;

class ProductsImageController extends Controller
{
    public function index()
    {
        $data = Products_image::all();
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],404);
        }
        return response()->json($data,200);
    }

    public function show($id)
    {
        $data = Products_image::find($id);
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
        $response = Products_image::create($data);
        return response()->json($response,201);
    }

    public function update(Request $request, Products_image $data)
    {
        $data->update($request->all());
        return response()->json($data,200);
    }

    public function delete($id){
        $data = Products_image::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

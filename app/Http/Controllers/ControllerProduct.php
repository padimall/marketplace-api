<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;

class ControllerProduct extends Controller
{
    public function index()
    {
        $data = Product::all();
        return response()->json($data,200);
    }

    public function show($id)
    {
        $data = Product::find($id);
        return response()->json($data,200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $response = Product::create($data);
        return response()->json($response,201);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($request->all());
        return response()->json($product,200);
    }

    public function delete($id){
        $product = Product::find($id);
        $response = $product->delete();
        return response()->json($response,200);
    }
}

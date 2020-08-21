<?php

namespace App\Http\Controllers;

use App\Agents_affiliate_supplier;
use Illuminate\Http\Request;

class AgentsAffiliateSupplierController extends Controller
{
    public function index()
    {
        $data = Agents_affiliate_supplier::all();
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],404);
        }
        return response()->json($data,200);
    }

    public function show($id)
    {
        $data = Agents_affiliate_supplier::find($id);
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
        $response = Agents_affiliate_supplier::create($data);
        return response()->json($response,201);
    }

    public function update(Request $request, Agents_affiliate_supplier $data)
    {
        $data->update($request->all());
        return response()->json($data,200);
    }

    public function delete($id){
        $data = Agents_affiliate_supplier::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

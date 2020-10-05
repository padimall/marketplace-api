<?php

namespace App\Http\Controllers;

use App\Logistic;
use Illuminate\Http\Request;

class LogisticController extends Controller
{
    public function index()
    {
        $data = Logistic::all();
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],200);
        }
        return response()->json($data,200);
    }

    public function show($id)
    {
        $data = Logistic::find($id);
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
        $response = Logistic::create($data);
        return response()->json($response,201);
    }

    public function update(Request $request, Logistic $data)
    {
        $data->update($request->all());
        return response()->json($data,200);
    }

    public function delete($id){
        $data = Logistic::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Admin_price;

class AdminPriceController extends Controller
{
    public function showAll()
    {
        $data = Admin_price::all();

        if(sizeOf($data)==0){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }

    public function show(Request $request)
    {
        $request->validate([
            'target_id' => 'required'
        ]);

        $data = Admin_price::find($request['target_id']);
        if(is_null($data)){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],200);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Resource found!',
            'data' => $data
        ],200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'up_to_price' => 'required|numeric',
            'addition_price' => 'required|numeric',
        ]);

        $data = $request->all();
        $response = Admin_price::create($data);

        return response()->json([
            'status' => 1,
            'message' => 'Resource created!'
        ],201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'target_id' => 'required'
        ]);

        $data = Admin_price::find($request['target_id']);

        if(!is_null($request['up_to_price'])){
            $request->validate([
                'up_to_price' => 'required|numeric'
            ]);
            $data->up_to_price = $request['up_to_price'];
        }

        if(!is_null($request['addition_price'])){
            $request->validate([
                'addition_price' => 'required|numeric'
            ]);
            $data->addition_price = $request['addition_price'];
        }

        $data->save();
        return response()->json([
            'status' => 1,
            'message' => 'Resource updated!'
        ],200);
    }

    public function delete($id){
        $data = Admin_price::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

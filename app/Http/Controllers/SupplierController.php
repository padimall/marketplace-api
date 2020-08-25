<?php

namespace App\Http\Controllers;

use App\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request){
        $request->validate([
            'request_type'=>'required'
        ]);

        $type = $request['request_type'];
        $data = new Request($request['data']);
        if($type == 1){
            return $this->showAll();
        }
        else if($type == 2){
            return $this->show($data);
        }
        else if($type == 3){
            return $this->store($data);
        }
        else if($type == 4){
            return $this->update($data);
        }
    }

    public function showAll()
    {
        $data = Supplier::all();
        if(is_null($data)){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],404);
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

        $data = Supplier::find($request['target_id']);
        if(is_null($data)){
            return response()->json([
                'status' => 0,
                'message' => 'Resource not found!'
            ],404);
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
            'buyer_id' => 'required|exists:buyers,id',
            'name' => 'required',
            'phone' => 'required|unique:suppliers,phone'
        ]);

        $data = $request->all();
        $response = Supplier::create($data);
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

        $data = Supplier::find($request['target_id']);

        if(!is_null($request['buyer_id'])){
            $request->validate([
                'buyer_id' => 'required|exists:buyers,id'
            ]);
            $data->buyer_id = $request['buyer_id'];
        }

        if(!is_null($request['name'])){
            $request->validate([
                'name' => 'required'
            ]);
            $data->name = $request['name'];
        }

        if(!is_null($request['phone'])){
            $request->validate([
                'phone' => 'required|unique:agents,phone'
            ]);
            $data->phone = $request['phone'];
        }
        $data->save();
        return response()->json([
            'status' => 1,
            'message' => 'Resource updated!'
        ],200);
    }

    public function delete($id){
        $data = Supplier::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

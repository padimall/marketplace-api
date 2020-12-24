<?php

namespace App\Http\Controllers;

use App\Payment;
use Xendit\Xendit;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function showAll(){
        $data = Payment::all();
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

    public function show($id)
    {
        $data = Payment::find($id);
        if(is_null($data)){
            return response()->json([
                'message' => 'Resource not found!'
            ],200);
        }
        return response()->json($data,200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'gate' => 'required|string',
            'method'=>'required|string',
            'method_code'=> 'required|string',
            'status'=>'required|integer'
        ]);

        $data = $request->all();

        $response = Payment::create($data);

        return response()->json([
            'status' => 1,
            'message' => 'Resource created!'
        ],201);

    }

    public function update(Request $request)
    {
        $request->validate([
            'target_id' => 'required|string',
        ]);

        $data = Payment::find($request['target_id']);

        if(!is_null($request['gate'])){
            $request->validate([
                'gate' => 'required'
            ]);
            $data->gate = $request['gate'];
        }

        if(!is_null($request['method'])){
            $request->validate([
                'method' => 'required'
            ]);
            $data->method = $request['method'];
        }

        if(!is_null($request['method_code'])){
            $request->validate([
                'method_code' => 'required'
            ]);
            $data->method_code = $request['method_code'];
        }

        if(!is_null($request['status'])){
            $request->validate([
                'status' => 'required'
            ]);
            $data->status = $request['status'];
        }
    }

    public function delete($id){
        $data = Payment::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

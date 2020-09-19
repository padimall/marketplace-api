<?php

namespace App\Http\Controllers;

use App\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request){
        $request->validate([
            'request_type'=>'required'
        ]);

        $type = $request['request_type'];
        if($type == 1){
            return $this->showAll();
        }
        else if($type == 2){
            return $this->show($request);
        }
        else if($type == 3){
            return $this->store($request);
        }
        else if($type == 4){
            return $this->update($request);
        }
        else if($type == 5){
            return $this->showLimit($request);
        }
    }

    public function showAll()
    {
        $data = Cart::all();
        if(sizeOf($data)==0){
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

    public function showLimit(Request $request)
    {
        $request->validate([
            'limit' => 'required'
        ]);
        $data = Cart::inRandomOrder()->limit($request['limit'])->get();
        if(sizeOf($data)==0){
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

        $data = Cart::find($request['target_id']);
        if(sizeOf($data)==0){
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
        //edit here
        $request->validate([
            'user_id'=> 'required|exists:users,id',
            'product_id'=> 'required|exists:products,id',
            'quantity'=> 'required',
            'status'=> 'required',
        ]);

        $data = $request->all();
        $response = Cart::create($data);
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

        $data = Cart::find($request['target_id']);

        if(!is_null($request['user_id'])){
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);
            $data->user_id = $request['user_id'];
        }

        if(!is_null($request['product_id'])){
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);
            $data->product_id = $request['product_id'];
        }

        if(!is_null($request['quantity'])){
            $request->validate([
                'quantity' => 'required'
            ]);
            $data->quantity = $request['quantity'];
        }

        if(!is_null($request['status'])){
            $request->validate([
                'status' => 'required'
            ]);
            $data->status = $request['status'];
        }

        $data->save();
        return response()->json([
            'status' => 1,
            'message' => 'Resource updated!'
        ],200);
    }

    public function list(Request $request)
    {
        $request->validate([
            'target_id' => 'required'
        ]);

        $data = DB::table('carts')
                ->select('*')
                ->where('user_id',$request['target_id'])
                ->get();

        if(sizeOf($data)== 0){
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

    public function delete($id){
        $data = Cart::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}

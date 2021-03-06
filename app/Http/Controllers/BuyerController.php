<?php

namespace App\Http\Controllers;

use App\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuyerController extends Controller
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
        $data = Buyer::all();
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

    public function showLimit(Request $request)
    {
        $request->validate([
            'limit' => 'required'
        ]);

        $data = Buyer::inRandomOrder()->limit($request['limit'])->get();
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

        $data = Buyer::find($request['target_id']);
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
            'username' => 'required|unique:buyers,username',
            'password' => 'required',
            'email' => 'required|email|unique:buyers,email',
            'address' => 'required',
            'phone' => 'required|unique:buyers,phone'
        ]);

        $request['password'] = md5($request['password']);

        $data = $request->all();
        $response = Buyer::create($data);
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

        $data = Buyer::find($request['target_id']);

        if(!is_null($request['password'])){
            $request->validate([
                'password' => 'required'
            ]);
            $data->password = md5($request['password']);
        }

        if(!is_null($request['email'])){
            $request->validate([
                'email' => 'required|email|unique:buyers,email'
            ]);
            $data->email = $request['email'];
        }

        if(!is_null($request['address'])){
            $request->validate([
                'address' => 'required'
            ]);
            $data->address = $request['address'];
        }

        if(!is_null($request['phone'])){
            $request->validate([
                'phone' => 'required|unique:buyers,phone'
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
        $data = Buyer::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }

    public function signin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $response = DB::table('buyers')
                    ->where('email',$request['email'])
                    ->where('password',hash('sha256',$request['password']))
                    ->get();
        if(!$response)
            return response()->json([
                'response' => $response,
                'message' => 'Username or password not found!'
            ], 200);

        return response()->json([
            'status' => $response,
            'message' => 'Login success'
        ]);

    }
}

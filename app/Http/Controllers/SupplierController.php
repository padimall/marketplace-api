<?php

namespace App\Http\Controllers;

use App\Supplier;
use App\Agent;
use App\Agents_affiliate_supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class SupplierController extends Controller
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
    }

    public function showAll()
    {
        $data = Supplier::all();
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
        $data = Supplier::where('user_id',request()->user()->id)->first();

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
            'name' => 'required',
            'phone' => 'required|unique:suppliers,phone',
            'address'=> 'required|string',
            'nib' => 'required',
            'image'=> 'mimes:png,jpg,jpeg|max:2048',
            'agent_code' => 'required'
        ]);

        $agentExist = Agent::where('user_id',request()->user()->id)->first();

        if(!is_null($agentExist)){
            return response()->json([
                'status' => 0,
                'message' => 'Agent Exist!'
            ],422);
        }

        $supplierExist = Supplier::where('user_id',request()->user()->id)->first();

        if(!is_null($supplierExist)){
            return response()->json([
                'status' => 0,
                'message' => 'Supplier Exist!'
            ],422);
        }

        $agent_data = Agent::where('agent_code',$request['agent_code'])->first();

        if(is_null($agent_data)){
            return response()->json([
                'status' => 0,
                'message' => 'Agent Not Exist!'
            ],404);
        }

        if(!is_null($request['image']))
        {
            $filename = 'supplier-'.Str::uuid().'.jpg';
            $request->file('image')->move(public_path("/supplier"),$filename);
            $imageURL = 'supplier/'.$filename;
        }

        $data = $request->all();
        $data['image'] = $imageURL;
        $data['user_id'] = request()->user()->id;
        $response = Supplier::create($data);

        $agent_affiliate_supplier = array(
            'supplier_id' => $response['id'],
            'agent_id' => $agent_data['id']
        );

        $save_affiliate = Agents_affiliate_supplier::create($agent_affiliate_supplier);

        return response()->json([
            'status' => 1,
            'message' => 'Resource created!'
        ],201);
    }

    public function update(Request $request)
    {

        $data = Supplier::where('user_id',request()->user()->id)->first();

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

        if(!is_null($request['address'])){
            $request->validate([
                'address' => 'required'
            ]);
            $data->address = $request['address'];
        }

        if(!is_null($request['image'])){
            $request->validate([
                'image' => 'required|mimes:png,jpg,jpeg|max:2048'
            ]);
            $image_target = $data->image;
            if(File::exists(public_path($image_target)))
            {
                $status = File::delete(public_path($image_target));
            }

            $filename = 'supplier-'.Str::uuid().'.jpg';
            $request->file('image')->move(public_path("/supplier"),$filename);
            $imageURL = 'supplier/'.$filename;

            $data->image = $imageURL;
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

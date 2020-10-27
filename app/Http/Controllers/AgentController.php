<?php

namespace App\Http\Controllers;

use App\Agent;
use App\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class AgentController extends Controller
{
    public function showAll()
    {
        $data = Agent::all();
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

        $data = Agent::inRandomOrder()->limit($request['limit'])->get();
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

    public function show()
    {
        $data = Agent::where('user_id',request()->user()->id)->first();

        if(is_null($data)){
            return response()->json([
                'status' => 0,
                'message' => 'You are not an agent!'
            ],200);
        }

        $supplier = DB::table('suppliers')
                    ->join('agents_affiliate_suppliers','agents_affiliate_suppliers.supplier_id','=','suppliers.id')
                    ->where('agents_affiliate_suppliers.agent_id',$data['id'])
                    ->select('suppliers.*')
                    ->get();
        $data['supplier'] = $supplier;

        if(!is_null($data['image']))
        {
            $data['image']=url('/').'/'.$data['image'];
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
            'phone' => 'required|unique:agents,phone',
            'address' => 'required|string',
            'image'=> 'mimes:png,jpg,jpeg|max:2008'
        ]);

        $supplierExist = Supplier::where('user_id',request()->user()->id)->first();

        if(!is_null($supplierExist)){
            return response()->json([
                'status' => 0,
                'message' => 'Supplier Exist!'
            ],422);
        }

        $agentExist = Agent::where('user_id',request()->user()->id)->first();

        if(!is_null($agentExist)){
            return response()->json([
                'status' => 0,
                'message' => 'Agent Exist!'
            ],422);
        }

        $random = strtolower(Str::random(5));
        while(Agent::where('agent_code',$random)->first()){
            $random = strtolower(Str::random(5));
        }

        $data = $request->all();
        $data['user_id'] = request()->user()->id;
        $data['agent_code'] = $random;
        $data['status'] = 0;

        if(!is_null($request['image']))
        {
            $filename = 'agent-'.Str::uuid().'.jpg';
            $request->file('image')->move(public_path("/agent"),$filename);
            $imageURL = 'agent/'.$filename;
            $data['image'] = $imageURL;
        }

        $response = Agent::create($data);
        return response()->json([
            'status' => 1,
            'message' => 'Resource created!'
        ],201);
    }

    public function update(Request $request)
    {

        $data = Agent::where('user_id',request()->user()->id)->first();

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
                'address' => 'required|strings'
            ]);
            $data->address = $request['address'];
        }

        if(!is_null($request['image'])){
            $request->validate([
                'image' => 'required|mimes:png,jpg,jpeg|max:2008'
            ]);

            $image_target = $data->image;
            if(File::exists(public_path($image_target)))
            {
                $status = File::delete(public_path($image_target));
            }

            $filename = 'agent-'.Str::uuid().'.jpg';
            $request->file('image')->move(public_path("/agent"),$filename);
            $imageURL = 'agent/'.$filename;

            $data->image = $imageURL;
        }

        $data->save();
        return response()->json([
            'status' => 1,
            'message' => 'Resource updated!'
        ],200);
    }

    public function delete($id){
        $data = Agent::find($id);
        $response = $data->delete();
        return response()->json($response,200);
    }
}
